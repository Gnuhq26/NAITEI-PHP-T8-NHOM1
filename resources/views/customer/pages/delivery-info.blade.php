@extends('customer.layouts.app')

@section('title', __('Delivery Information') . ' - Furniro')

@section('hero')
<section class="hero-section">
	<div class="hero-content">
		<h1>{{ __('Delivery Information') }}</h1>
		<div class="breadcrumb">
			<a href="{{ route('customer.categories') }}">{{ __('Home') }}</a>
			<i class="fas fa-chevron-right"></i>
			<a href="{{ route('customer.cart.index') }}">{{ __('Cart') }}</a>
			<i class="fas fa-chevron-right"></i>
			<span>{{ __('Delivery Info') }}</span>
		</div>
	</div>
</section>
@endsection

@section('content')
<div class="container">
    <div class="delivery-wrapper">
        <!-- Delivery Information Form -->
        <div class="delivery-main">
            <div class="delivery-form">
                <h2>{{ __('Delivery Information') }}</h2>
                
                <form action="{{ route('customer.delivery.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-grid">
                        <!-- Full Name -->
                        <div class="form-group full-width">
                            <label for="user_name">{{ __('Full Name') }} <span class="required">*</span></label>
                            <input type="text" 
                                   id="user_name" 
                                   name="user_name" 
                                   value="{{ old('user_name', $deliveryInfo['user_name']) }}"
                                   class="form-input"
                                   required>
                            @error('user_name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group full-width">
                            <label for="email">{{ __('Email Address') }} <span class="required">*</span></label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $deliveryInfo['email']) }}"
                                   class="form-input"
                                   required>
                            @error('email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="form-group">
                            <label for="phone_number">{{ __('Phone Number') }} <span class="required">*</span></label>
                            <input type="tel" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   value="{{ old('phone_number', $deliveryInfo['phone_number']) }}"
                                   class="form-input"
                                   required>
                            @error('phone_number')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div class="form-group">
                            <label for="country">{{ __('Country') }} <span class="required">*</span></label>
                            <input type="text" 
                                   id="country" 
                                   name="country" 
                                   value="{{ old('country', $deliveryInfo['country']) }}"
                                   class="form-input"
                                   required>
                            @error('country')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="form-group">
                            <label for="city">{{ __('City') }} <span class="required">*</span></label>
                            <input type="text" 
                                   id="city" 
                                   name="city" 
                                   value="{{ old('city', $deliveryInfo['city']) }}"
                                   class="form-input"
                                   required>
                            @error('city')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- District -->
                        <div class="form-group">
                            <label for="district">{{ __('District') }} <span class="required">*</span></label>
                            <input type="text" 
                                   id="district" 
                                   name="district" 
                                   value="{{ old('district', $deliveryInfo['district']) }}"
                                   class="form-input"
                                   required>
                            @error('district')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Ward -->
                        <div class="form-group">
                            <label for="ward">{{ __('Ward') }}</label>
                            <input type="text" 
                                   id="ward" 
                                   name="ward" 
                                   value="{{ old('ward', $deliveryInfo['ward']) }}"
                                   class="form-input"
                                   placeholder="{{ __('Enter ward/commune') }}">
                            @error('ward')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        </div>

                    <!-- Navigation Buttons -->
                    <div class="form-actions">
                        <a href="{{ route('customer.cart.index') }}" class="btn-secondary">
                            {{ __('Back to Cart') }}
                        </a>
                        <button type="submit" class="btn-primary">
                            {{ __('Continue to Checkout') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="delivery-summary">
            <h3>{{ __('Order Summary') }}</h3>
            
            <!-- Cart Items -->
            <div class="summary-items">
                @foreach($cart as $productId => $item)
                    <div class="summary-item">
                        <div class="item-info">
                            <p class="item-name">{{ $item['name'] }}</p>
                            <p class="item-qty">{{ __('Qty') }}: {{ $item['quantity'] }}</p>
                        </div>
                        <p class="item-price">
                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} VND
                        </p>
                    </div>
                @endforeach
            </div>

            <!-- Summary Totals -->
            <div class="summary-row">
                <span>{{ __('Items') }} ({{ $totalQuantity }})</span>
                <span>{{ number_format($totalPrice, 0, ',', '.') }} VND</span>
            </div>
            <div class="summary-row">
                <span>{{ __('Delivery') }}</span>
                @if(isset($shippingInfo))
                    <span class="{{ $shippingInfo['is_free_shipping'] ? 'free-delivery' : '' }}">
                        @if($shippingInfo['is_free_shipping'])
                            {{ __('Free') }}
                        @else
                            {{ number_format($shippingInfo['shipping_fee'], 0, ',', '.') }} VND
                        @endif
                    </span>
                @else
                    <span class="free-delivery">{{ __('Free') }}</span>
                @endif
            </div>
            <div class="summary-row total">
                <span>{{ __('Total') }}</span>
                <span>{{ number_format($shippingInfo['total'] ?? $totalPrice, 0, '.', ',') }} VND</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Main Layout */
.delivery-wrapper {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 40px;
}

.delivery-main {
    background: #fff;
}

.delivery-form {
    background: #fff;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.delivery-form h2 {
    color: #3A3A3A;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 24px;
    border-bottom: 2px solid #F9F1E7;
    padding-bottom: 12px;
}

/* Form Styling */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    color: #3A3A3A;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
}

.required {
    color: #EF4444;
}

.form-input {
    padding: 12px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: #B88E2F;
    box-shadow: 0 0 0 3px rgba(184, 142, 47, 0.1);
}

.error-message {
    color: #EF4444;
    font-size: 12px;
    margin-top: 4px;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 24px;
    border-top: 1px solid #F3F4F6;
}

.btn-primary {
    background: #B88E2F;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #A67F2A;
}

.btn-secondary {
    background: #6B7280;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-secondary:hover {
    background: #4B5563;
}

/* Order Summary */
.delivery-summary {
    background: #F9F1E7;
    padding: 24px;
    border-radius: 8px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.delivery-summary h3 {
    color: #3A3A3A;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
}

.summary-items {
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.item-info {
    flex: 1;
}

.item-name {
    color: #3A3A3A;
    font-weight: 500;
    font-size: 14px;
    margin-bottom: 4px;
}

.item-qty {
    color: #666;
    font-size: 12px;
}

.item-price {
    color: #3A3A3A;
    font-weight: 600;
    font-size: 14px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    color: #333;
    font-size: 14px;
}

.summary-row.total {
    font-weight: 700;
    color: #3A3A3A;
    font-size: 16px;
    border-bottom: none;
    padding-top: 16px;
    border-top: 2px solid #3A3A3A;
}

.free-delivery {
    color: #10B981;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .delivery-wrapper {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .delivery-summary {
        position: static;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 12px;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
        text-align: center;
    }
}
</style>
@endpush
