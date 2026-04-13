<?php

namespace Tests\Feature\Billing;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_checkout_updates_users_subscription_plan(): void
    {
        $user = User::factory()->create();
        $freePlan = $this->makePlan([
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('checkout', $freePlan));

        $response->assertRedirect(route('dashboard'));
        $this->assertSame($freePlan->id, $user->refresh()->subscription_plan_id);
    }

    public function test_swap_without_subscription_redirects_to_checkout(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan([
            'slug' => 'photographer',
            'stripe_price_id' => 'price_monthly_123',
            'price' => 9.99,
        ]);

        $response = $this->actingAs($user)->post(route('subscription.swap', $plan), [
            'interval' => 'month',
        ]);

        $response->assertRedirect(route('checkout', ['plan' => $plan->id, 'interval' => 'month']));
    }

    public function test_cancel_without_active_subscription_redirects_back_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('subscription.cancel'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'No active subscription found.');
    }

    public function test_resume_without_subscription_redirects_back_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('subscription.resume'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'No resumable subscription found.');
    }

    private function makePlan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Starter',
            'slug' => (string) Str::uuid(),
            'stripe_product_id' => null,
            'stripe_price_id' => null,
            'price' => 0,
            'annual_price' => 0,
            'annual_stripe_price_id' => null,
            'annual_discount_percent' => 0,
            'photo_limit' => 100,
            'collection_limit' => 5,
            'private_collections' => false,
            'watermarking' => false,
            'selling' => false,
            'video_upload' => false,
            'custom_templates' => false,
            'upload_logo' => false,
            'features' => ['Basic'],
            'active' => true,
            'sort_order' => 1,
            'recommended' => false,
        ], $overrides));
    }
}
