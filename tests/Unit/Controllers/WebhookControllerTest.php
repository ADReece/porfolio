<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\WebhookController;
use Mockery;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_payment_intent_failed_marks_order_failed(): void
    {
        $order = new class {
            public array $updates = [];

            public function update(array $data): bool
            {
                $this->updates[] = $data;
                return true;
            }
        };

        $query = Mockery::mock();
        $query->shouldReceive('first')->once()->andReturn($order);

        $orderAlias = Mockery::mock('alias:App\\Models\\Order');
        $orderAlias->shouldReceive('where')
            ->once()
            ->with('stripe_payment_intent_id', 'pi_failed_123')
            ->andReturn($query);

        $controller = new WebhookController();
        $response = $controller->handlePaymentIntentFailed([
            'data' => [
                'object' => [
                    'id' => 'pi_failed_123',
                ],
            ],
        ]);

        $this->assertEquals(['status' => 'failed'], $order->updates[0]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_payment_intent_succeeded_marks_order_completed_when_found(): void
    {
        $order = new class {
            public array $updates = [];
            public object $user;

            public function __construct()
            {
                $this->user = (object) [
                    'stripe_connect_enabled' => false,
                ];
            }

            public function update(array $data): bool
            {
                $this->updates[] = $data;
                return true;
            }
        };

        $query = Mockery::mock();
        $query->shouldReceive('first')->once()->andReturn($order);

        $orderAlias = Mockery::mock('alias:App\\Models\\Order');
        $orderAlias->shouldReceive('where')
            ->once()
            ->with('stripe_payment_intent_id', 'pi_success_123')
            ->andReturn($query);

        $controller = new WebhookController();
        $response = $controller->handlePaymentIntentSucceeded([
            'data' => [
                'object' => [
                    'id' => 'pi_success_123',
                ],
            ],
        ]);

        $this->assertSame('completed', $order->updates[0]['status']);
        $this->assertArrayHasKey('paid_at', $order->updates[0]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
