<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function checkout()
    {
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();
        if ($cartItems->isEmpty()) {
            return $this->error('Cart is empty', 400);
        }

        $totalPrice = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // Clear cart
            Cart::where('user_id', Auth::id())->delete();

            DB::commit();
            return $this->success($order, 'Order placed successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error('Order failed: ' . $e->getMessage(), 500);
        }
    }

    public function orderHistory()
    {
        $orders = Order::where('user_id', Auth::id())->with('items.product')->get();
        return $this->success($orders, 'Order history retrieved successfully');
    }
}
