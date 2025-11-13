<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCollectionLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->subscriptionPlan) {
            return redirect()->route('pricing')
                ->with('error', 'Please select a subscription plan to create collections.');
        }

        if (!$user->canCreateCollections()) {
            $limit = $user->subscriptionPlan->collection_limit;
            return redirect()->back()
                ->with('error', "You've reached your collection limit of {$limit} collections. Please upgrade your plan to create more.");
        }

        return $next($request);
    }
}
