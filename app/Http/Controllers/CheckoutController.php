<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\DeliveryInfo;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\NewOrderPlaced;
use Illuminate\Support\Facades\Redis;

class CheckoutController extends Controller
{
    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Show delivery information form
     */
    public function deliveryInfo(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.cart.index')->with('info', __('Your cart is empty.'));
        }

        $user = $request->user();
        
        // Pre-fill delivery info from user data
        $deliveryInfo = [
            'user_name' => $user->name ?? '',
            'email' => $user->email ?? '',
            'phone_number' => $user->phone_number ?? '',
            'country' => $user->country ?? '',
            'city' => $user->city ?? '',
            'district' => $user->district ?? '',
            'ward' => $user->ward ?? '',
        ];

        $totalQuantity = array_sum(array_map(function ($item) {
            return (int) ($item['quantity'] ?? 0);
        }, $cart));

        $totalPrice = array_reduce($cart, function ($carry, $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            return $carry + ($quantity * $price);
        }, 0.0);

        $shippingInfo = $this->shippingService->getShippingInfo($totalPrice);

        return view('customer.pages.delivery-info', compact('cart', 'deliveryInfo', 'totalQuantity', 'totalPrice', 'shippingInfo'));
    }

    /**
     * Store delivery information in session and redirect to checkout
     */
    public function storeDeliveryInfo(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'nullable|string',
        ]);

        // Store delivery info in session (for current order only)
        $request->session()->put('delivery_info', $request->only([
            'user_name', 'email', 'phone_number', 'country', 'city', 'district', 'ward'
        ]));

        // Do NOT update user profile - keep user data unchanged
        // User profile will only change when user updates their personal profile

        return redirect()->route('customer.checkout.create');
    }

    /**
     * Show the checkout page with cart summary
     */
    public function create(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.cart.index')->with('success', __('Your cart is empty.'));
        }

        // Check if delivery info is provided
        $deliveryInfo = $request->session()->get('delivery_info');
        if (!$deliveryInfo) {
            return redirect()->route('customer.delivery.info')->with('info', __('Please provide delivery information first.'));
        }

        $totalQuantity = array_sum(array_map(function ($item) {
            return (int) ($item['quantity'] ?? 0);
        }, $cart));

        $totalPrice = array_reduce($cart, function ($carry, $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            return $carry + ($quantity * $price);
        }, 0.0);

        $shippingInfo = $this->shippingService->getShippingInfo($totalPrice);

        return view('customer.pages.checkout', compact('cart', 'deliveryInfo', 'totalQuantity', 'totalPrice', 'shippingInfo'));
    }

    /**
     * Place order from session cart
     */
    public function store(Request $request)
    {
        // Remove payment method validation - only COD supported now

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('customer.cart.index')->with('info', __('Your cart is empty.'));
        }

        // Check if delivery info is provided
        $deliveryInfo = $request->session()->get('delivery_info');
        if (!$deliveryInfo) {
            return redirect()->route('customer.delivery.info')->with('info', __('Please provide delivery information first.'));
        }

        // Process COD payment directly
        return $this->processCODPayment($request);
    }



    private function processCODPayment(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $productIds = array_map('intval', array_keys($cart));

        // Load products keyed by primary key (product_id)
        $products = Product::whereIn('product_id', $productIds)->get()->keyBy('product_id');

        // Validate all items exist and have sufficient stock
        $computedTotal = 0.0;
        foreach ($cart as $pid => $item) {
            $pid = (int) $pid;
            $product = $products[$pid] ?? null;
            if (!$product) {
                return back()->with('error', __('A product in your cart is no longer available.'));
            }
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($quantity <= 0) {
                return back()->with('error', __('Invalid item quantity in cart.'));
            }
            if (isset($product->stock) && $product->stock !== null && $product->stock < $quantity) {
                return back()->with('error', __('Insufficient stock for product: ') . $product->name);
            }
            $computedTotal += $quantity * (float) $product->price;
        }

        $shippingInfo = $this->shippingService->getShippingInfo($computedTotal);
        $finalTotal = $shippingInfo['total'];

        $userId = (int) $request->user()->getKey();
        $newOrderId = null;

        DB::transaction(function () use ($cart, $products, $computedTotal, $shippingInfo, $finalTotal, $userId, $request, &$newOrderId) {
            // Create order
            $order = Order::create([
                'customer_id' => $userId,
                'order_date' => now()->toDateString(),
                'total_cost' => $finalTotal, // Tổng tiền bao gồm phí ship
                'shipping_fee' => $shippingInfo['shipping_fee'], // Phí ship riêng biệt
                'status' => 'pending',

            ]);
            
            $newOrderId = $order->order_id;

            // Create delivery info if exists in session
            $deliveryInfo = $request->session()->get('delivery_info');
            if ($deliveryInfo) {
                DeliveryInfo::create([
                    'order_id' => $order->order_id,
                    'user_name' => $deliveryInfo['user_name'],
                    'email' => $deliveryInfo['email'],
                    'phone_number' => $deliveryInfo['phone_number'],
                    'country' => $deliveryInfo['country'],
                    'city' => $deliveryInfo['city'],
                    'district' => $deliveryInfo['district'],
                    'ward' => $deliveryInfo['ward'] ?? null,
                ]);
            }

            // Create items and decrement stock
            foreach ($cart as $pid => $item) {
                $pid = (int) $pid;
                $product = $products[$pid];
                $quantity = (int) $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);

                if (isset($product->stock) && $product->stock !== null) {
                    // Prevent race conditions by checking stock again and updating
                    $affected = Product::where('product_id', $product->getKey())
                        ->where('stock', '>=', $quantity)
                        ->decrement('stock', $quantity);
                    if ($affected === 0) {
                        throw new \RuntimeException('Insufficient stock during checkout.');
                    }
                }
            }
          
            // Clear cart and delivery info from session
            $request->session()->forget(['cart', 'delivery_info']);

            // Eager load the user relationship before dispatching the event
            $order->load('user');

            // Dispatch the event after the order is successfully created
            event(new NewOrderPlaced($order));
        });   
             
        return redirect()->route('customer.orders', ['highlight' => $newOrderId])->with('success', __('Order placed successfully.'));
    }
} 
