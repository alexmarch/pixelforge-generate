<?php

use App\Models\User;
use App\Services\Jwt;

it('generates and parses a signed token for a user', function () {
    config()->set('jwt.key', 'FkL2+V+1k2auI3xxTz/2skChDQVVjT9PW1/grXafg3M=');
    $jwt = new Jwt;
    $user = new User;
    $user->id = 42;

    $token = $jwt->generate($user);

    expect($jwt->getId($token))->toBe(42);
});
