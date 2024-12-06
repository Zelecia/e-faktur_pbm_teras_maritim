<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipeFakturPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only users with the 'admin' role can view this resource
        return $user->hasRole('Administrator');
    }

    /**
     * Other policy methods (view, create, update, delete, etc.)
     */
}
