<?php

namespace App\Policies;

use App\Models\Set;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SetPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Set $set): bool
    {
        return $user->id === $set->collection->user_id;
    }

    public function update(User $user, Set $set): bool
    {
        return $user->id === $set->collection->user_id;
    }

    public function delete(User $user, Set $set): bool
    {
        return $user->id === $set->collection->user_id;
    }
}

