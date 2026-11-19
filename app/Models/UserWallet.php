<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use JsonSerializable;

class UserWallet extends Model implements JsonSerializable
{
    protected $fillable = ['balance', 'currency'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format the balance as a currency string.
     */
    protected function formattedBalance(): Attribute
    {
        return Attribute::make(
            get: fn () => Number::currency(
                $this->balance,
                $this->currency,
                precision: $this->balance < 1 ? 6 : 2
            )
        );
    }

    /**
     * Get the JSON representation of the user wallet.
     */
    public function jsonSerialize(): array
    {
        return [
            'balance' => $this->balance,
            'currency' => $this->currency,
            'formatted_balance' => $this->formatted_balance,
        ];
    }

    /**
     * Deduct the specified amount from the wallet balance.
     */
    public function deductBalance(float $amount): void
    {
        if ($this->balance < $amount) {
            throw new \InvalidArgumentException('Insufficient balance.');
        }
        $this->update([
            'balance' => $this->balance - $amount,
        ]);
        $this->refresh();
    }
}
