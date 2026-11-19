<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CreateUserWallet extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        if ($user && !$user->wallet) {
            $user->wallet()->create([
                'balance' => 100,
                'currency' => 'USD',
            ]);
        }
    }
}
