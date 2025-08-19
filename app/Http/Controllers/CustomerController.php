<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display the categories page for customers
     */
    public function categories()
    {
        $categories = Category::withCount('products')->paginate(10);
        
        return view('customer.pages.categories', compact('categories'));
    }

    /**
     * Display products in a specific category
     */
    public function products($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $products = Product::where('category_id', $categoryId)
                          ->with('category')
                          ->get();
        
        return view('customer.pages.products', compact('category', 'products'));
    }

    /**
     * Display the about page
     */
    public function about()
    {
        return view('customer.pages.about');
    }

    /**
     * Display the contact page
     */
    public function contact()
    {
        return view('customer.pages.contact');
    }

    /**
     * Display the order history page for customers
     */
    public function orders(Request $request)
    {
        $customer = Auth::user();
        
        // Get orders with their items and status history
        $orders = Order::with(['orderItems.product', 'statusOrders'])
                      ->where('customer_id', $customer->id)
                      ->orderBy('created_at', 'desc');            
        $orders = Order::with([
            'orderItems.product' => function ($query) {
                $query->select('product_id', 'name', 'price'); // Add other needed columns here
            },
            'statusOrders'
        ])
        ->where('customer_id', $customer->id)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        
        return view('customer.pages.orders', compact('orders'));
    }

    /**
     * Display detailed information about a specific order
     */
    public function orderDetails($orderId)
    {
        $customer = Auth::user();
        
        // Get specific order with all related data
        $order = Order::with(['orderItems.product', 'statusOrders.admin', 'user'])
              ->where('customer_id', $customer->id)
              ->findOrFail($orderId);
        
        return view('customer.pages.order-details', compact('order'));
    }

    public function cancelOrder($orderId, Request $request)
    {
        $customer = Auth::user();
        
        $order = Order::where('customer_id', $customer->id)
                     ->where('order_id', $orderId)
                     ->first();
        
        if (!$order) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Order not found')
                ], 404);
            }
            return redirect()->route('customer.orders')->with('error', __('Order not found'));
        }
        
        // Check if order can be cancelled

        if (!in_array($order->status, ['pending', 'confirmed', 'approved'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('This order cannot be cancelled')
                ], 400);
            }
            return redirect()->route('customer.orders.details', $orderId)->with('error', __('This order cannot be cancelled'));
        }
        
        try {
            // Update order status to cancelled
            $order->update(['status' => 'cancelled']);
            
            // Add status history
            $order->statusOrders()->create([
                'action_type' => 'cancelled',
                'date' => now(),
                'admin_id' => null
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Order has been cancelled successfully')
                ]);
            }
            return redirect()->route('customer.orders.details', $orderId)->with('success', __('Order has been cancelled successfully'));
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('An error occurred while cancelling the order')
                ], 500);
            }
            return redirect()->route('customer.orders.details', $orderId)->with('error', __('An error occurred while cancelling the order'));
        }
    }
}
