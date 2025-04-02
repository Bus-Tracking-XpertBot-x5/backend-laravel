<?php

namespace App\Policies;

use App\Models\BusMovement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BusMovementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        
        return $user->role==="admin" ;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BusMovement $busMovement): bool
    {
        if($user->role==="admin"){
            return true;
        }
        return $user->role==="manager" && $busMovement->organization_id===$user->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BusMovement $busMovement): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BusMovement $busMovement): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BusMovement $busMovement): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BusMovement $busMovement): bool
    {
        return false;
    }
}
