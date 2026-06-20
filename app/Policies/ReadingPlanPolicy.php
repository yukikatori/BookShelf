<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReadingPlanPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }

    public function complete(User $user, ReadingPlan $plan): bool
    {
        return $user->id === $plan->user_id;
    }
}
