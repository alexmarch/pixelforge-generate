<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePostRequest;
use App\Jobs\Pixelforge as JobsPixelforge;
use App\Models\Pixelforge;
use App\Services\Pixelforge as ServicePixelforge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected ServicePixelforge $pixelforgeService
    ) {}

    public function show(Request $request): InertiaResponse
    {
        return inertia('Dashboard', [
            'wallet' => Auth::user()->wallet ?? null,
        ]);
    }

    public function wallet(): JsonResponse
    {
        $wallet = Auth::user()->wallet ?? null;

        return response()->json([
            'wallet' => $wallet,
        ]);
    }

    public function generate(GeneratePostRequest $request): RedirectResponse
    {
        $size = $request->input('size');
        $quality = $request->input('quality');
        $reference_images = $request->input('reference_images', []);
        $data = $request->input('data', null);

        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (! $wallet) {
                return back()->with('error', 'Wallet not found');
            }

            $price = $this->pixelforgeService->calculatePrice(
                $size,
                $quality,
                count($reference_images),
                strlen($request->input('prompt', ''))
            )->cost;

            if ($wallet->balance < $price) {
                return back()->with('error', 'Insufficient balance');
            }

            $response = $this->pixelforgeService->generate(
                $request->input('prompt', ''),
                $size,
                $quality,
                $reference_images,
                $data
            );

            Log::info('PixelForge generation response', [
                'response' => $response,
            ]);

            $attributes = [
                'gen_id' => $response['id'] ?? null,
                'status' => $response['status'] ?? null,
                'prompt' => $response['prompt'] ?? null,
                'size' => $response['size'] ?? null,
                'quality' => $response['quality'] ?? null,
                'reference_images' => $response['reference_images'] ?? null,
                'metadata' => $response['metadata'] ?? null,
                'user_id' => Auth::id(),
                'error' => $response['error'] ?? null,
                'object' => $response['object'] ?? null,
                'price' => $price,
            ];

            Log::info('Pixelforge attributes before creation', [
                'attributes' => $attributes,
            ]);

            foreach ($attributes as $key => $value) {
                if (is_object($value)) {
                    Log::error('Object passed to Pixelforge::create', [
                        'attribute' => $key,
                        'class' => get_class($value),
                    ]);
                }
            }

            $pixelforge = Pixelforge::create($attributes);

            JobsPixelforge::dispatch($pixelforge);

            return redirect()->route('dashboard.generations');
        } catch (\Exception $e) {
            Log::error('PixelForge generation failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        Log::info('Pixelforge webhook payload', [
            'payload' => $payload,
        ]);
        $signature = [];

        foreach (explode(',', (string) $request->header('Pixelforge-Signature')) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
            $signature[$key] = $value;
        }

        $timestamp = (int) ($signature['t'] ?? 0);
        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", config('services.pixelforge.webhook_secret'));

        if (! hash_equals($expected, $signature['v1'] ?? '') || abs(time() - $timestamp) > 300) {
            abort(400, 'Invalid Pixelforge signature.');
        }

        $event = json_decode($payload, true);

        Log::info('Pixelforge webhook received', [
            'event' => $event,
        ]);

        $gen_id = $event['id'] ?? null;
        if (! $gen_id) {
            Log::warning('Webhook event missing generation ID');

            return response()->noContent();
        }

        $pixelforge = Pixelforge::where('gen_id', $gen_id)->first();
        if (! $pixelforge) {
            Log::warning('Pixelforge generation not found', ['gen_id' => $gen_id]);

            return response()->noContent();
        }

        $status = $event['status'] ?? null;
        if (! $status) {
            Log::warning('Webhook event missing status', ['gen_id' => $gen_id]);

            return response()->noContent();
        }

        $pixelforge->update([
            'status' => $status,
            'image_url' => $event['image_url'] ?? $pixelforge->image_url,
            'image_url_expires_at' => $event['image_url_expires_at'] ?? $pixelforge->image_url_expires_at,
            'completed_at' => $event['completed_at'] ?? ($status === 'succeeded' ? now() : $pixelforge->completed_at),
            'error' => $event['error'] ?? ($status === 'failed' ? ['error' => 'Generation failed'] : $pixelforge->error),
        ]);

        Log::info('Pixelforge generation updated via webhook', [
            'gen_id' => $gen_id,
            'status' => $status,
        ]);

        return response()->noContent();
    }

    public function price(GeneratePostRequest $request): JsonResponse
    {
        // Handle the price request here
        $size = $request->input('size');
        $quality = $request->input('quality');
        $reference_images = $request->input('reference_images', []);
        $data = $request->input('data', null);
        $price = $this->pixelforgeService->calculatePrice(
            $size,
            $quality,
            count($reference_images),
            strlen($request->input('prompt', ''))
        )->formatCost();

        return response()->json(['price' => $price]);
    }

    public function generations(): InertiaResponse
    {
        $user = Auth::user();
        $generations = $user->pixelforge()
            ->where(function ($query) {
                $query->where('image_url_expires_at', '>', now())
                    ->orWhereIn('status', ['pending', 'queued', 'processing', 'completed', 'succeeded', 'failed']);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return inertia('Generations', [
            'generations' => $generations,
        ]);
    }
}
