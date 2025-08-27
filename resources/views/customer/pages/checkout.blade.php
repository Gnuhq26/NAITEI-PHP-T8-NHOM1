@extends('customer.layouts.app')

@section('title', __('Checkout') . ' - Furniro')

@section('hero')
<section class="hero-section">
    <div class="hero-content">
        <h1>{{ __('Checkout') }}</h1>
        <div class="breadcrumb">
            <a href="{{ route('customer.categories') }}">{{ __('Home') }}</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('customer.cart.index') }}">{{ __('Cart') }}</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('customer.delivery.info') }}">{{ __('Delivery Info') }}</a>
            <i class="fas fa-chevron-right"></i>
            <span>{{ __('Checkout') }}</span>
        </div>
    </div>
</section>
@endsection

@section('content')
@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

@if(session('info'))
    <div class="alert-info">{{ session('info') }}</div>
@endif

@if ($errors->any())
    <div class="alert-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="checkout-wrapper">
    <div class="checkout-main">
        <!-- Order Items -->
        <div class="section-card">
            <h3>{{ __('Order Items') }}</h3>
            <div class="cart-table">
                <div class="cart-header">
                    <div class="col-product">{{ __('Product') }}</div>
                    <div class="col-price">{{ __('Price') }}</div>
                    <div class="col-quantity">{{ __('Quantity') }}</div>
                    <div class="col-subtotal">{{ __('Subtotal') }}</div>
                </div>
                
                @foreach($cart as $item)
                <div class="cart-row">
                    <div class="col-product">
                        <img src="{{ isset($item['image']) ? asset($item['image']) : asset('images/default-product.svg') }}" alt="{{ $item['name'] }}">
                        <div class="info">
                            <div class="name">{{ $item['name'] }}</div>
                            <div class="sku">#{{ $item['id'] }}</div>
                        </div>
                    </div>
                    <div class="col-price">{{ number_format($item['price'], 0, '.', ',') }} {{ __('VND') }}</div>
                    <div class="col-quantity">x {{ $item['quantity'] }}</div>
                    <div class="col-subtotal">{{ number_format($item['price'] * $item['quantity'], 0, '.', ',') }} {{ __('VND') }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="section-card">
            <h3>{{ __('Delivery Information') }}</h3>
            <div class="delivery-info-display">
                <div class="info-row">
                    <span class="label">{{ __('Full Name') }}:</span>
                    <span class="value">{{ $deliveryInfo['user_name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="label">{{ __('Email') }}:</span>
                    <span class="value">{{ $deliveryInfo['email'] }}</span>
                </div>
                <div class="info-row">
                    <span class="label">{{ __('Phone Number') }}:</span>
                    <span class="value">{{ $deliveryInfo['phone_number'] }}</span>
                </div>
                <div class="info-row">
                    <span class="label">{{ __('Address') }}:</span>
                    <span class="value">
                        {{ $deliveryInfo['ward'] ? $deliveryInfo['ward'] . ', ' : '' }}{{ $deliveryInfo['district'] }}, {{ $deliveryInfo['city'] }}, {{ $deliveryInfo['country'] }}
                    </span>
                </div>
            </div>
            <a href="{{ route('customer.delivery.info') }}" class="edit-link">{{ __('Edit Delivery Info') }}</a>
        </div>

        <!-- Payment Method -->
        <div class="section-card">
            <h3>{{ __('Payment Method') }}</h3>
            <form action="{{ route('customer.checkout.store') }}" method="POST" id="checkoutForm">
                @csrf
                <div class="payment-methods">
                    <div class="payment-option">
                        <input type="radio" id="cod" name="payment_method" value="cod" checked style="display: none;">
                        <label for="cod" class="payment-label">
                            <div class="payment-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="payment-details">
                                <h4>{{ __('Cash on Delivery (COD)') }}</h4>
                                <p>{{ __('Pay when you receive your order') }}</p>
                            </div>
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="checkout-summary">
        <h3>{{ __('Order Summary') }}</h3>
        <div class="summary-row">
            <span>{{ __('Items') }}</span>
            <span>{{ $totalQuantity ?? 0 }}</span>
        </div>
        <div class="summary-row">
            <span>{{ __('Delivery') }}</span>
            <span class="free-delivery">{{ __('Free') }}</span>
        </div>
        <div class="summary-row total">
            <span>{{ __('Total') }}</span>
            <span>{{ number_format($totalPrice ?? 0, 0, '.', ',') }} {{ __('VND') }}</span>
        </div>
        <button type="button" class="btn-primary full" onclick="showPlaceOrderModal()">{{ __('Place Order') }}</button>
    </div>
</div>

@include('customer.components.modals', [
    'modalId' => 'placeOrderModal',
    'title' => 'Confirm Order',
    'message' => 'Are you sure you want to place this order?',
    'confirmText' => 'Place Order',
    'cancelText' => 'Cancel',
    'confirmClass' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
])
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

.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
}

.alert-info {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
}

.alert-error ul {
    margin: 0;
    padding-left: 20px;
}

.checkout-wrapper {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.checkout-main {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.section-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 24px;
}

.section-card h3 {
    color: #3A3A3A;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    border-bottom: 2px solid #F9F1E7;
    padding-bottom: 12px;
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
    grid-template-columns: 1.2fr 0.5fr 0.5fr 0.6fr;
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

/* Delivery Information Display */
.delivery-info-display {
    background: #F9F1E7;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 16px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    color: #3A3A3A;
    min-width: 120px;
}

.info-row .value {
    color: #666;
    text-align: right;
    flex: 1;
}

.edit-link {
    color: #B88E2F;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
}

.edit-link:hover {
    text-decoration: underline;
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-option {
    position: relative;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.payment-label {
    display: flex;
    align-items: center;
    padding: 16px;
    border: 2px solid #E5E5E5;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
}

.payment-option input[type="radio"]:checked + .payment-label {
    border-color: #B88E2F;
    background: rgba(184, 142, 47, 0.05);
}

.payment-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #F9F1E7;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 20px;
    color: #B88E2F;
}

.payment-details h4 {
    margin: 0 0 4px 0;
    color: #3A3A3A;
    font-size: 16px;
    font-weight: 600;
}

.payment-details p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.checkout-summary {
    background: #F9F1E7;
    padding: 24px;
    border-radius: 8px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.free-delivery {
    color: #10B981;
    font-weight: 600;
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

.btn-primary.full {
    width: 100%;
    margin-top: 16px;
}

@media (max-width: 900px) {
    .checkout-wrapper {
        grid-template-columns: 1fr;
    }
    
    .checkout-summary {
        position: static;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .info-row .value {
        text-align: left;
    }
}
</style>
@endpush
@push('scripts')
<script>
function showPlaceOrderModal() {
    document.getElementById('placeOrderModal').style.display = 'flex';
    
    const confirmBtn = document.getElementById('placeOrderModal_confirm');
    confirmBtn.onclick = function() {
        document.getElementById('checkoutForm').submit();
    };
}
</script>
@endpush
