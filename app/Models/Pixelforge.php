<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;

class Pixelforge extends Model
{
    protected $fillable = [
        'gen_id',
        'status',
        'prompt',
        'size',
        'quality',
        'reference_images',
        'metadata',
        'image_url',
        'image_url_expires_at',
        'completed_at',
        'user_id',
        'error',
        'object',
        'price',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'reference_images' => 'array',
            'metadata' => 'array',
            'error' => 'array',
        ];
    }

    /**
     * Format the price as a currency string.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => Number::currency(
                $this->price,
                'USD',
                precision: 6
            )
        );
    }

    /**
     * Format image_url_expires_at as ISO 8601 string for client-side timezone handling.
     */
    protected function formattedImageUrlExpiresAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_url_expires_at ? Date::parse($this->image_url_expires_at)->toIso8601String() : null
        );
    }

    /**
     * Get the array representation of the model for serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'gen_id' => $this->gen_id,
            'status' => $this->status,
            'prompt' => $this->prompt,
            'size' => $this->size,
            'quality' => $this->quality,
            'reference_images' => $this->reference_images,
            'metadata' => $this->metadata,
            'image_url' => $this->image_url,
            'image_url_expires_at' => $this->image_url_expires_at,
            'completed_at' => $this->completed_at,
            'error' => $this->error,
            'object' => $this->object,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the JSON representation of the Pixelforge model.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
