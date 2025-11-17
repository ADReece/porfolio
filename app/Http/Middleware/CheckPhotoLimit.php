<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPhotoLimit
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
                ->with('error', 'Please select a subscription plan to upload photos.');
        }

        if (!$user->canUploadPhotos()) {
            $limit = $user->subscriptionPlan->photo_limit;
            return redirect()->back()
                ->with('error', "You've reached your photo limit of {$limit} photos. Please upgrade your plan to upload more.");
        }

        return $next($request);
    }
}
