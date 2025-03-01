<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Event;
use App\Models\Order;
use App\Traits\ApiResponseTrait;

class StripeWebhookController extends Controller
{
    use ApiResponseTrait;

    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentSuccess($paymentIntent);
                    break;

                case 'charge.refunded':
                    $refund = $event->data->object;
                    $this->handleRefundSuccess($refund);
                    break;

                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }

            return response()->json(['message' => 'Webhook received'], 200);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook failed'], 400);
        }
    }

    private function handlePaymentSuccess($paymentIntent)
    {
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update(['status' => 'paid']);
            Log::info('Payment successful for order ID: ' . $order->id);
        }
    }

    private function handleRefundSuccess($refund)
    {
        $order = Order::where('payment_intent_id', $refund->payment_intent)->first();

        if ($order) {
            $order->update(['status' => 'refunded']);
            Log::info('Refund successful for order ID: ' . $order->id);
        }
    }
}
