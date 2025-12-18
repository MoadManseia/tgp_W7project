<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * View orders for the author's own books.
     */
    public function index()
    {
        $user = Auth::user();

        // Get all book IDs that belong to this author
        $authorBookIds = $user->books()->pluck('books.id');

        if ($authorBookIds->isEmpty()) {
            return response()->json([
                'orders' => []
            ]);
        }

        // Get order items that contain the author's books
        $orderItemIds = OrderItem::whereIn('book_id', $authorBookIds)
            ->pluck('order_id')
            ->unique();

        // Get orders that contain the author's books
        $orders = Order::whereIn('id', $orderItemIds)
            ->with(['user', 'items' => function ($query) use ($authorBookIds) {
                $query->whereIn('book_id', $authorBookIds)->with('book');
            }, 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter order items to only show the author's books
        $orders->transform(function ($order) use ($authorBookIds) {
            $order->items = $order->items->filter(function ($item) use ($authorBookIds) {
                return $authorBookIds->contains($item->book_id);
            });
            return $order;
        });

        return response()->json([
            'orders' => $orders
        ]);
    }
}

