<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
	protected $shippingService;

	public function __construct(ShippingService $shippingService)
	{
		$this->shippingService = $shippingService;
	}

	/**
	 * Display the cart page for customers.
	 */
	public function index(Request $request)
	{
		$currentCart = $request->session()->get('cart', []);
		$cart = [];
		$totalQuantity = 0;
		$totalPrice = 0.0;
		$productsInCart = Product::whereIn('product_id', array_keys($currentCart))->get()->keyBy('product_id');

		foreach ($currentCart as $productId => $item) {
			$product = $productsInCart->get($productId);

			if ($product && $product->stock > 0) {
				$quantity = min($item['quantity'], $product->stock); // Ensure quantity doesn't exceed stock
				$cart[$productId] = [
					'product_id' => $productId,
					'name' => $product->name,
					'price' => $product->price,
					'image' => $product->image ?? '/images/default-product.svg',
					'quantity' => $quantity,
				];
				$totalQuantity += $quantity;
				$totalPrice += $quantity * $product->price;
			} else {
				// Optionally, add a flash message for removed/unavailable products
				session()->flash('warning', __('Some products in your cart are no longer available or out of stock.'));
			}
		}

		$request->session()->put('cart', $cart);

		$shippingInfo = $this->shippingService->getShippingInfo($totalPrice);
		$amountForFreeShipping = $this->shippingService->getAmountForFreeShipping($totalPrice);

		return view('customer.pages.cart', compact(
			'cart', 
			'totalQuantity', 
			'totalPrice', 
			'shippingInfo',
			'amountForFreeShipping'
		));
	}

	/**
	 * Add a product to the cart (session-based).
	 */
	public function add(Request $request, Product $product)
	{
		$validated = $request->validate([
			'quantity' => 'nullable|integer|min:1',
		]);

		$quantityToAdd = max(1, (int) ($validated['quantity'] ?? 1));

		// Check if the product has enough stock
		// if ($product->stock < $quantityToAdd) {
		// 	return back()->with('error', __('Not enough stock for this product. Available: :stock', ['stock' => $product->stock]));
		// }

		$cart = $request->session()->get('cart', []);
		$productId = $product->product_id;

		if (isset($cart[$productId])) {
			$existingQuantity = (int) ($cart[$productId]['quantity'] ?? 0);
			$newTotalQuantity = $existingQuantity + $quantityToAdd;
			if ($newTotalQuantity > $product->stock) {
				return back()->with('error', __('Adding this quantity would exceed available stock. Max allowed: :max', ['max' => $product->stock - $existingQuantity]));
			}
			$cart[$productId]['quantity'] = $newTotalQuantity;
		} else {
			$cart[$productId] = [
				'product_id' => $productId,
				'name' => $product->name,
				'price' => $product->price,
				'image' => $product->image ?? '/images/default-product.svg',
				'quantity' => $quantityToAdd,
				'stock' => $product->stock, // Store stock for immediate checks in cart view
			];
		}

		$request->session()->put('cart', $cart);

		return back()->with('success', __('Product added to cart.'));
	}

	/**
	 * Update the quantity of a cart item.
	 */
	public function update(Request $request, Product $product)
	{
		$validated = $request->validate([
			'quantity' => 'required|integer|min:0',
		]);

		$newQuantity = (int) $validated['quantity'];
		$cart = $request->session()->get('cart', []);
		$productId = $product->product_id;

		if ($newQuantity <= 0) {
			unset($cart[$productId]);
		} else {
			if (isset($cart[$productId])) {
				// Check if the new quantity exceeds the product's current stock
				if ($newQuantity > $product->stock) {
					return back()->with('error', __('Quantity cannot exceed available stock. Max available: :stock', ['stock' => $product->stock]));
				}
				$cart[$productId]['quantity'] = $newQuantity;
			}
		}

		$request->session()->put('cart', $cart);

		return back()->with('success', __('Cart updated.'));
	}

	/**
	 * Remove a product from the cart.
	 */
	public function remove(Request $request, Product $product)
	{
		$cart = $request->session()->get('cart', []);
		$productId = $product->getKey();
		unset($cart[$productId]);
		$request->session()->put('cart', $cart);

		return back()->with('success', __('Product removed from cart.'));
	}

	/**
	 * Clear all items in the cart.
	 */
	public function clear(Request $request)
	{
		$request->session()->forget('cart');
		return back()->with('success', __('Cart cleared.'));
	}
} 
