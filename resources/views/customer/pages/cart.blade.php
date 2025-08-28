@extends('customer.layouts.app')

@section('title', __('Cart') . ' - Furniro')

@section('hero')
<section class="hero-section">
	<div class="hero-content">
		<h1>{{ __('Cart') }}</h1>
		<div class="breadcrumb">
			<a href="{{ route('customer.categories') }}">{{ __('Home') }}</a>
			<i class="fas fa-chevron-right"></i>
			<span>{{ __('Cart') }}</span>
		</div>
	</div>
</section>
@endsection

@section('content')
@if(session('success'))
	<div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('warning'))
	<div class="alert-warning">{{ session('warning') }}</div>
@endif
@if(session('error'))
	<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="cart-wrapper">
	<div class="cart-main">
		@if(empty($cart))
			<div class="cart-empty">
				<p>{{ __('Your cart is empty.') }}</p>
				<a href="{{ route('customer.categories') }}" class="btn-primary">{{ __('Continue Shopping') }}</a>
			</div>
		@else
			<div class="cart-table">
				<div class="cart-header">
					<div class="col-product">{{ __('Product') }}</div>
					<div class="col-price">{{ __('Price') }}</div>
					<div class="col-quantity">{{ __('Quantity') }}</div>
					<div class="col-subtotal">{{ __('Subtotal') }}</div>
					<div class="col-action"></div>
				</div>

				@foreach($cart as $item)
					<div class="cart-row">
						<div class="col-product">
							<img src="{{ asset($item['image'] ?? 'images/default-product.svg') }}" alt="{{ $item['name'] }}">
							<div class="info">
								<div class="name">{{ $item['name'] }}</div>
								<div class="sku">#{{ $item['product_id'] }}</div>
							</div>
						</div>
						<div class="col-price">{{ number_format($item['price'], 0, '.', ',') }} {{ __('VND') }}</div>
						<div class="col-quantity">
							<form action="{{ route('customer.cart.update', ['product' => $item['product_id']]) }}" method="POST" class="qty-form">
								@csrf
								<input type="number" name="quantity" min="0" value="{{ $item['quantity'] }}">
								<button type="submit" class="btn-ghost">{{ __('Update') }}</button>
							</form>
						</div>
						<div class="col-subtotal">{{ number_format($item['price'] * $item['quantity'], 0, '.', ',') }} {{ __('VND') }}</div>
						<div class="col-action">
							<form action="{{ route('customer.cart.remove', ['product' => $item['product_id']]) }}" method="POST">
								@csrf
								@method('DELETE')
								<button type="submit" class="btn-icon" title="{{ __('Remove') }}">
									<i class="fas fa-times"></i>
								</button>
							</form>
						</div>
					</div>
				@endforeach
			</div>

			<div class="cart-actions">
				<a href="{{ route('customer.categories') }}" class="btn-outline">{{ __('Continue Shopping') }}</a>
				<form action="{{ route('customer.cart.clear') }}" method="POST">
					@csrf
					<button type="submit" class="btn-outline danger">{{ __('Clear Cart') }}</button>
				</form>
			</div>
		@endif
	</div>

	<div class="cart-summary">
		<h3>{{ __('Cart Totals') }}</h3>
		<div class="summary-row">
			<span>{{ __('Items') }}</span>
			<span>{{ $totalQuantity ?? 0 }}</span>
		</div>
		<div class="summary-row">
			<span>{{ __('Subtotal') }}</span>
			<span>{{ number_format($totalPrice ?? 0, 0, '.', ',') }} {{ __('VND') }}</span>
		</div>
		
		@if(isset($shippingInfo))
			<div class="summary-row">
				<span>{{ __('Shipping Fee') }}</span>
				<span class="{{ $shippingInfo['is_free_shipping'] ? 'free-shipping' : '' }}">
					@if($shippingInfo['is_free_shipping'])
						{{ __('Free') }}
					@else
						{{ number_format($shippingInfo['shipping_fee'], 0, '.', ',') }} {{ __('VND') }}
					@endif
				</span>
			</div>
			
			@if(isset($amountForFreeShipping) && $amountForFreeShipping > 0)
				<div class="free-shipping-notice">
					<i class="fas fa-truck"></i>
					{{ __('Add') }} {{ number_format($amountForFreeShipping, 0, '.', ',') }} {{ __('VND') }} {{ __('more for free shipping!') }}
				</div>
			@elseif($shippingInfo['is_free_shipping'])
				<div class="free-shipping-notice success">
					<i class="fas fa-check-circle"></i>
					{{ __('You got free shipping!') }}
				</div>
			@endif
		@endif
		
		<div class="summary-row total">
			<span>{{ __('Total') }}</span>
			<span>{{ number_format($shippingInfo['total'] ?? $totalPrice ?? 0, 0, '.', ',') }} {{ __('VND') }}</span>
		</div>
		<br>
		<a href="{{ route('customer.delivery.info') }}" class="btn-primary full">{{ __('Proceed to Delivery Info') }}</a>
	</div>
</div>
@endsection

@push('styles')
<style>
.alert-success {
	background: #ecfdf5;
	color: #065f46;
	border: 1px solid #a7f3d0;
	padding: 12px 16px;
	border-radius: 6px;
	margin-bottom: 16px;
}
.alert-warning {
	background: #fffbeb;
	color: #967b1f;
	border: 1px solid #fde68a;
	padding: 12px 16px;
	border-radius: 6px;
	margin-bottom: 16px;
}
.alert-error {
	background: #fef3f2;
	color: #991b1b;
	border: 1px solid #fca5a5;
	padding: 12px 16px;
	border-radius: 6px;
	margin-bottom: 16px;
}
.cart-wrapper {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 24px;
}
.cart-main { }
.cart-summary {
	background: #F9F1E7;
	padding: 24px;
	border-radius: 8px;
	height: fit-content;
}
.cart-summary h3 {
	margin-bottom: 16px;
	color: #3A3A3A;
}
.summary-row {
	display: flex;
	justify-content: space-between;
	padding: 10px 0;
	border-bottom: 1px solid #eee;
	color: #333;
}
.summary-row.total {
	font-weight: 700;
	color: #3A3A3A;
}
.cart-empty {
	text-align: center;
	padding: 60px 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 12px;
}

.cart-table {
	width: 100%;
	background: #fff;
	border: 1px solid #eee;
	border-radius: 8px;
	overflow: hidden;
}

.cart-header,
.cart-row {
	display: grid;
	grid-template-columns: 1.2fr .5fr .6fr .6fr .2fr;
	align-items: center;
	gap: 16px;
	padding: 14px 16px;
}

.cart-header {
	background: #F9F1E7;
	font-weight: 600;
	color: #3A3A3A;
}

.cart-row {
	border-top: 1px solid #f1f1f1;
}

.col-product {
	display: flex;
	align-items: center;
	gap: 12px;
}

.col-product img {
	width: 64px;
	height: 64px;
	object-fit: cover;
	border-radius: 8px;
	border: 1px solid #eee;
}

.col-product .info .name {
	font-weight: 600;
	color: #3A3A3A;
}

.col-product .info .sku {
	color: #999;
	font-size: 12px;
	margin-top: 2px;
}

.col-price,
.col-subtotal {
	color: #3A3A3A;
	font-weight: 600;
}

.qty-form {
	display: flex;
	align-items: center;
	gap: 8px;
}

.qty-form input[type="number"] {
	width: 80px;
	padding: 8px;
	border: 1px solid #ddd;
	border-radius: 6px;
}

.btn-ghost {
	background: none;
	border: 1px solid #ddd;
	padding: 8px 12px;
	border-radius: 6px;
	cursor: pointer;
	color: #333;
}

.btn-icon {
	background: none;
	border: none;
	color: #999;
	cursor: pointer;
}

.btn-icon:hover {
	color: #E97171;
}

.cart-actions {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 12px;
	margin-top: 16px;
}

.btn-primary {
	background: #B88E2F;
	color: #fff;
	border: none;
	padding: 12px 20px;
	border-radius: 6px;
	text-decoration: none;
	cursor: pointer;
	font-weight: 600;
}

.btn-outline {
	background: none;
	border: 1px solid #ddd;
	padding: 12px 20px;
	border-radius: 6px;
	text-decoration: none;
	color: #333;
	cursor: pointer;
	font-weight: 600;
}

.btn-outline.danger {
	border-color: #f5c2c7;
	color: #b02a37;
}

.btn-primary.full {
	width: 100%;
	margin-top: 16px;
}

.free-shipping {
	color: #16a34a !important;
	font-weight: 600;
}

.free-shipping-notice {
	background: #fef3c7;
	color: #92400e;
	padding: 12px;
	border-radius: 6px;
	margin: 12px 0;
	font-size: 14px;
	display: flex;
	align-items: center;
	gap: 8px;
}

.free-shipping-notice.success {
	background: #dcfce7;
	color: #166534;
}

.free-shipping-notice i {
	font-size: 16px;
}
@media (max-width: 900px) {
    .cart-wrapper {
        grid-template-columns: 1fr;
    }
    .cart-header,
    .cart-row {
        grid-template-columns: 1fr 0.5fr 0.6fr 0.6fr 0.2fr;
    }
}

@media (max-width: 600px) {
    .cart-header {
        display: none;
    }
    .cart-row {
        grid-template-columns: 1fr;
        gap: 8px;
        align-items: flex-start;
    }
    .col-price,
    .col-quantity,
    .col-subtotal,
    .col-action {
        display: flex;
        justify-content: space-between;
    }
}
</style>
@endpush 
