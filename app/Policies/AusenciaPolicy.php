<?php

namespace App\Policies;

use App\Models\Ausencia;
use App\Models\User;

class AusenciaPolicy
{
    /**
     * Determine whether the user can view any absences.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the absence.
     */
    public function view(User $user, Ausencia $ausencia): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create absences.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the absence.
     * Rule: Regular teachers can only update their own absence if slot has not started and it is NOT covered yet.
     * Admin or directiva can update anytime.
     */
    public function update(User $user, Ausencia $ausencia): bool
    {
        return $ausencia->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the absence.
     * Rule: Regular teachers can only delete their own absence if slot has not started and it is NOT covered yet.
     * Admin or directiva can delete anytime.
     */
    public function delete(User $user, Ausencia $ausencia): bool
    {
        return $ausencia->canBeDeletedBy($user);
    }
}
