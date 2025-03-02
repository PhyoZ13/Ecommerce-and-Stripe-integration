<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    /*
        Stripe Docs:  https://docs.stripe.com/payments/checkout/how-checkout-works?payment-ui=embedded-form#branding
    */
    public function createCheckoutSession(Request $request) 
    {
        $order = Order::where('id', $request->order_id)->where('status', 'pending')->latest()->first();

        if (!$order) {
            return $this->error('No pending order found', 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];
        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->product->name,
                    ],
                    'unit_amount' => $item->price * 100,
                ],
                'quantity' => $item->quantity,
            ];
        }

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => url('/api/payment/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/api/payment/cancel'),
        ]);

        return $this->success(['session_id' => $checkoutSession->id], 'Payment session created');
    }

    public function paymentSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return $this->error('Invalid payment session', 400);
        }

        $order = Order::where('user_id', Auth::id())->where('status', 'pending')->latest()->first();
        if ($order) {
            $order->update(['status' => 'paid']);
        }

        return $this->success(null, 'Payment successful');
    }

    public function paymentCancel()
    {
        return $this->error('Payment cancelled', 400);
    }
}
