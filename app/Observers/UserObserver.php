<?php

namespace App\Observers;

use App\Models\SubscriptionPlan;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Automatically assign free plan to new users
        if (!$user->subscription_plan_id) {
            $freePlan = SubscriptionPlan::where('slug', 'free')->first();
            if ($freePlan) {
                $user->subscription_plan_id = $freePlan->id;
                $user->saveQuietly(); // Save without triggering events again
            }
        }
    }
}

