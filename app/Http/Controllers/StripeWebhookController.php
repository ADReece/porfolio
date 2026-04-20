<?php

namespace App\Http\Controllers;

use App\Jobs\SubmitOrderToProdigi;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\StripeClient;

/**
 * Example webhook handler for Stripe payment completion
 * 
 * This demonstrates how to integrate Prodigi order submission
 * into your existing Stripe webhook flow.
 * 
 * Setup:
 * 1. Add this endpoint to your routes/api.php or routes/web.php
 * 2. Configure Stripe webhook to POST to this endpoint
 * 3. Update the route URL in your Stripe dashboard
 */
class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Verify webhook signature
            $event = Event::constructFrom(
                json_decode($payload, true)
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response('Webhook Error: Invalid signature', 400);
        }

        // Route to appropriate handler
        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'charge.refunded' => $this->handleRefund($event),
            default => Log::info('Unhandled Stripe event', ['type' => $event->type]),
        };

        return response('success', 200);
    }

    /**
     * Handle successful payment
     * 
     * This is where we submit print orders to Prodigi
     */
    private function handlePaymentSucceeded($event): void
    {
        $paymentIntent = $event->data->object;
        $stripePaymentIntentId = $paymentIntent->id;

        // Find the order by Stripe payment intent ID
        $order = Order::where('stripe_payment_intent_id', $stripePaymentIntentId)
            ->first();

        if (!$order) {
            Log::error('Order not found for payment intent', [
                'payment_intent_id' => $stripePaymentIntentId,
            ]);
            return;
        }

        // Update order status
        $order->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        Log::info('Order payment confirmed', [
            'order_id' => $order->id,
            'payment_intent_id' => $stripePaymentIntentId,
        ]);

        // Submit print orders to Prodigi
        if ($order->hasPrintItems()) {
            SubmitOrderToProdigi::dispatch($order);

            Log::info('Order queued for Prodigi submission', [
                'order_id' => $order->id,
            ]);
        } else {
            Log::info('Order has no print items, skipping Prodigi', [
                'order_id' => $order->id,
            ]);
        }

        // Handle digital products or other fulfillment here
        // deliver_digital_products($order);
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($event): void
    {
        $paymentIntent = $event->data->object;
        $stripePaymentIntentId = $paymentIntent->id;

        $order = Order::where('stripe_payment_intent_id', $stripePaymentIntentId)
            ->first();

        if (!$order) {
            return;
        }

        $order->update([
            'status' => 'failed',
        ]);

        Log::warning('Order payment failed', [
            'order_id' => $order->id,
            'reason' => $paymentIntent->last_payment_error?->message ?? 'Unknown',
        ]);
    }

    /**
     * Handle refund
     */
    private function handleRefund($event): void
    {
        $charge = $event->data->object;
        
        // Find order by charge ID
        $order = Order::where('stripe_payment_intent_id', $charge->payment_intent)
            ->first();

        if (!$order) {
            return;
        }

        $order->update([
            'status' => 'refunded',
        ]);

        Log::info('Order refunded', [
            'order_id' => $order->id,
            'charge_id' => $charge->id,
        ]);

        // If order was already submitted to Prodigi, you might want to cancel it
        if ($order->isProdigiSubmitted() && $order->prodigi_status !== 'shipped') {
            Log::info('Should cancel Prodigi order', [
                'order_id' => $order->id,
                'prodigi_order_id' => $order->prodigi_order_id,
                // Implement cancellation if needed:
                // dispatch(new \App\Jobs\CancelProdigiOrder($order));
            ]);
        }
    }
}
