<?php

namespace App\Jobs;

use App\Events\BalanceUpdate;
use App\Events\GenerationStatusUpdate;
use App\Models\Pixelforge as ModelsPixelforge;
use App\Services\Pixelforge as ServicePixelforge;
use DateTimeInterface;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pixelforge implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ModelsPixelforge $pixelforge,
    ) {}

    /**
     * Execute the job.
     *
     * @param  ServicesPixelforge  $pixelforgeService
     */
    public function handle(
        ServicePixelforge $pixelforgeService
    ): void {
        $this->pixelforge->refresh();

        if (in_array($this->pixelforge->status, ['succeeded', 'failed'], true)) {
            return;
        }

        try {
            $result = $pixelforgeService->checkGeneration($this->pixelforge->gen_id);
            Log::info('PixelForge status check result', [
                'gen_id' => $this->pixelforge->gen_id,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            // Handle the exception, e.g., log it or update the status
            $this->pixelforge->update([
                'status' => 'failed',
                'error' => ['error' => $e->getMessage()],
            ]);
            Log::warning('PixelForge status check failed', [
                'gen_id' => $this->pixelforge->gen_id,
                'message' => $e->getMessage(),
            ]);

            $this->release(30);

            return;
        }

        if ($result['status'] === 'succeeded') {
            DB::transaction(function () use ($result) {
                $this->pixelforge->update([
                    'status' => 'succeeded',
                    'image_url' => $result['image_url'] ?? null,
                    'image_url_expires_at' => $result['image_url_expires_at'] ?? null,
                    'completed_at' => $result['completed_at'] ?? now(),
                ]);

                // Deduct balance from wallet
                $wallet = $this->pixelforge->user->wallet;
                if ($wallet) {
                    $wallet->deductBalance($this->pixelforge->price);
                    Log::info('Deducting balance from wallet', [
                        'user_id' => $this->pixelforge->user->id,
                        'amount' => $this->pixelforge->price,
                    ]);
                }
            });

            $this->pixelforge->refresh();
            
            Log::info('Dispatching GenerationStatusUpdate event', [
                'generation_id' => $this->pixelforge->id,
                'user_id' => $this->pixelforge->user->id,
                'channels' => ["generation.{$this->pixelforge->id}", "user.{$this->pixelforge->user->id}"],
            ]);
            GenerationStatusUpdate::dispatch($this->pixelforge);
            
            Log::info('Dispatching BalanceUpdate event', [
                'user_id' => $this->pixelforge->user->id,
                'channel' => "user.{$this->pixelforge->user->id}",
            ]);
            BalanceUpdate::dispatch($this->pixelforge->user);

            return;
        }

        if ($result['status'] === 'failed') {
            $this->pixelforge->update([
                'status' => 'failed',
                'error' => ['error' => $result['error'] ?? 'Generation failed'],
            ]);
            GenerationStatusUpdate::dispatch($this->pixelforge);

            return;
        }

        $this->release(30);
    }

    public function uniqueId(): string
    {
        return $this->pixelforge->gen_id;
    }

    public function debounceVia(): Repository
    {
        return Cache::driver('redis');
    }

    public function uniqueVia(): Repository
    {
        return Cache::driver('redis');
    }

    public function debounceId(): string
    {
        return $this->pixelforge->gen_id;
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->pixelforge->user->id))
                ->releaseAfter(60)
                ->expireAfter(120),
            new ThrottlesExceptionsWithRedis(
                5, // Maximum number of attempts
                60 // Decay time in seconds
            ),
        ];
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->plus(minutes: 30);
    }
}
