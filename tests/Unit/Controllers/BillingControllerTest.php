<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\BillingController;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_checkout_free_plan_updates_user_and_redirects_to_dashboard(): void
    {
        $controller = new BillingController();
        $request = Request::create('/checkout/free', 'GET');
        $plan = new SubscriptionPlan(['slug' => 'free']);
        $plan->id = 99;

        $user = Mockery::mock();
        $user->shouldReceive('update')->once()->with(['subscription_plan_id' => 99]);

        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = $controller->checkout($request, $plan);

        $this->assertEquals(route('dashboard'), $response->getTargetUrl());
    }

    public function test_checkout_paid_plan_returns_checkout_view(): void
    {
        $controller = new BillingController();
        $request = Request::create('/checkout/paid?interval=year', 'GET');
        $plan = new SubscriptionPlan(['id' => 1, 'slug' => 'photographer']);

        $response = $controller->checkout($request, $plan);

        $this->assertEquals('billing.checkout', $response->name());
        $this->assertSame('year', $response->getData()['interval']);
    }
}
