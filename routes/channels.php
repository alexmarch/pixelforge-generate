<?php

use App\Models\Pixelforge;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('generation.{id}', function (User $user, $id) {
    return (int) $user->id === Pixelforge::find($id)?->user_id;
});

Broadcast::channel('user.{id}.generations', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});
