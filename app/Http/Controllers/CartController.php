<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();
        return $this->success($cartItems, 'Cart retrieved successfully');
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id']
            ],
            [
                'quantity' => DB::raw("quantity + {$validated['quantity']}")
            ]
        );

        return $this->success($cartItem, 'Item added to cart', 201);
    }

    public function updateCart(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->update(['quantity' => $validated['quantity']]);

        return $this->success($cartItem, 'Cart updated successfully');
    }

    public function removeFromCart($id)
    {
        $cartItem = Cart::where('user_id', Auth::id())->findOrFail($id);
        $cartItem->delete();

        return $this->success(null, 'Item removed from cart');
    }
}
