<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Number;

class Pixelforge
{
    protected string $base_url = 'https://pixelforge.promptedit.dev/v1';
    protected string $api_key;
    protected string $webhook_secret;
    public float $cost;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->api_key = env('PIXELFORGE_API_KEY');
        $this->webhook_secret = env('PIXELFORGE_WEBHOOK_SECRET');
    }

    public function checkGeneration(string $gen_id)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->api_key,
            ])->get($this->base_url . '/generations/' . $gen_id);

            return $response->json();
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function generate(string $prompt, string $size, string $quality, array $reference_images, $data = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->api_key,
                'Idempotency-Key' => Str::uuid()->toString(),
            ])->post($this->base_url . '/generations', [
                'prompt' => $prompt,
                'size' => $size,
                'quality' => $quality,
                'reference_images' => $reference_images,
                'metadata' => $data,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function pricing()
    {
        $key = 'pixelforge.pricing.' . hash('sha256', $this->api_key);
        Cache::remember($key, now()->addMinutes(60), function () use ($key) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->api_key,
            ])->get($this->base_url . '/pricing');

            return $response->json();
        });
        return Cache::get($key);
    }

    public function calculatePrice(
        string $size,
        string $quality,
        int $num_reference_images = 0,
        int $num_text_characters = 0
    ): static {
        $pricing = $this->pricing();

        $charactersPerToken = max(
            1,
            (int) ($pricing['tokens']['characters_per_text_token'] ?? 4)
        );

        $rates = $pricing['rates'] ?? [];
        $tokens = $pricing['tokens'] ?? [];

        $textTokens = (int) ceil($num_text_characters / $charactersPerToken);
        $imageTokens = (int) ($tokens['per_reference_image'] ?? 0) * $num_reference_images;
        $outputTokens = (int) ($tokens['output'][$quality][$size] ?? 0);

        // Calculate reasoning tokens based on the minimum for the selected quality.
        $reasoningTokens = (int) ($tokens['reasoning'][$quality]['min'] ?? 0);

        $cost = (
            $textTokens * (float) ($rates['input_text_tokens'] ?? 0)
            + $imageTokens * (float) ($rates['input_image_tokens'] ?? 0)
            + $outputTokens * (float) ($rates['output_tokens'] ?? 0)
            + $reasoningTokens * (float) ($rates['reasoning_tokens'] ?? 0)
        ) / 1_000_000;

        $markup = (float) config('pixelforge.markup', 0);

        $this->cost = $cost * (1 + $markup);
        return $this;
    }

    public function formatCost(string $currency = 'USD'): string
    {
        return Number::currency($this->cost, $currency, precision: $this->cost < 1 ? 7 : 2);
    }
}
