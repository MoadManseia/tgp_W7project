<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        return new CartResource($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $book_id)
    {
        $user = Auth::user();

        // if user not have a cart
        $cart = Cart::where('user_id', $user->id)->firstOrCreate([
            'user_id' => $user->id,
            'payment_method_id' => PaymentMethod::first()->id,
            'address' => $user->customer->address
        ]);

        $cartItem = CartItem::where('cart_id', $cart->id)->where('book_id', $book_id)->first();

        if ($cartItem) {
            $cartItem->update([
                'qty' => $cartItem->qty + 1
            ]);
        } else {
            // add item to the cart
            $cart->items()->create([
                'book_id' => $book_id,
                'qty' => 1
            ]);
        }



        return response()->json([
            'message' => 'item added'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Decrease quantity for the specific item (opposite to store).
     */
    public function decreaseQty(Request $request, $book_id)
    {
        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)->where('book_id', $book_id)->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Item not found in cart'
            ], 404);
        }

        if ($cartItem->qty > 1) {
            $cartItem->update([
                'qty' => $cartItem->qty - 1
            ]);
            return response()->json([
                'message' => 'Quantity decreased'
            ]);
        } else {
            // If quantity is 1, remove the item from cart
            $cartItem->delete();
            return response()->json([
                'message' => 'Item removed from cart'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Checkout - Create order from cart and clear cart.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)->with('items.book')->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calculate total
            $total = $cart->totalCart();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'payment_method_id' => $cart->payment_method_id,
                'address' => $cart->address,
                'total' => $total,
                'status' => 'pending' // or whatever default status you use
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $cartItem->book_id,
                    'qty' => $cartItem->qty,
                    'price' => $cartItem->book->price
                ]);
            }

            // Clear cart items
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('items')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart address.
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address' => ['required', 'string', 'max:255']
        ]);

        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        $cart->update([
            'address' => $request->address
        ]);

        return response()->json([
            'message' => 'Address updated successfully',
            'cart' => new CartResource($cart)
        ]);
    }

    /**
     * View orders that are checked out.
     */
    public function viewOrders()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->with(['items.book', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
    }
}
