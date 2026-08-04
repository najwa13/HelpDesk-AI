<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class CategoryPolicy
{
    public function manage(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
