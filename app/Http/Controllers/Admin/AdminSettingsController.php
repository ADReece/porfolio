<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $plans = SubscriptionPlan::orderBy('sort_order')->get();

        return view('admin.settings.index', compact('settings', 'plans'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:500',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    public function updatePlans(Request $request)
    {
        $validated = $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|exists:subscription_plans,id',
            'plans.*.stripe_product_id' => 'nullable|string|max:255',
            'plans.*.stripe_price_id' => 'nullable|string|max:255',
            'plans.*.annual_stripe_price_id' => 'nullable|string|max:255',
        ]);

        foreach ($validated['plans'] as $planData) {
            SubscriptionPlan::where('id', $planData['id'])->update([
                'stripe_product_id' => $planData['stripe_product_id'],
                'stripe_price_id' => $planData['stripe_price_id'],
                'annual_stripe_price_id' => $planData['annual_stripe_price_id'],
            ]);
        }

        return redirect()->back()->with('success', 'Subscription plan Stripe IDs updated successfully!');
    }
}

