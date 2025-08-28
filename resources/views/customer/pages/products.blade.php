@extends('customer.layouts.app')

@section('title', $category->name . ' Products - Furniro')

@section('hero')
<section class="hero-section">
    <div class="hero-content">
        <h1>{{ $category->name }}</h1>
    </div>
</section>
@endsection

@section('content')
<!-- Filter Bar -->
<div class="filter-bar">
    <div class="filter-left">
        <form method="GET" action="{{ route('customer.products', $category->category_id) }}" class="search-form">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('Search products...') }}"
                       class="search-input">
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                @if(request('price_range'))
                    <input type="hidden" name="price_range" value="{{ request('price_range') }}">
                @endif
                <button type="submit" class="search-btn">{{ __('Search') }}</button>
            </div>
        </form>
    </div>
    
    <div class="filter-right">
        <span class="showing-text">
            @if(request('search'))
                {{ __('Found') }} {{ $products->count() }} {{ __('products for') }} "{{ request('search') }}"
            @else
                {{ __('Showing') }} 1-{{ $products->count() }} {{ __('of') }} {{ $products->count() }} {{ __('products') }}
            @endif
        </span>
        
        <div style="display: flex; gap: 15px; align-items: center;">
            <label>{{ __('Price Range') }}</label>
            <select class="price-select" onchange="this.form.submit()" form="filter-form">
                <option value="" {{ !request('price_range') ? 'selected' : '' }}>{{ __('All Prices') }}</option>
                <option value="under_1m" {{ request('price_range') == 'under_1m' ? 'selected' : '' }}>{{ __('Under 1M VND') }}</option>
                <option value="1m_2m" {{ request('price_range') == '1m_2m' ? 'selected' : '' }}>{{ __('1M - 2M VND') }}</option>
                <option value="2m_3m" {{ request('price_range') == '2m_3m' ? 'selected' : '' }}>{{ __('2M - 3M VND') }}</option>
                <option value="3m_4m" {{ request('price_range') == '3m_4m' ? 'selected' : '' }}>{{ __('3M - 4M VND') }}</option>
                <option value="4m_5m" {{ request('price_range') == '4m_5m' ? 'selected' : '' }}>{{ __('4M - 5M VND') }}</option>
                <option value="5m_6m" {{ request('price_range') == '5m_6m' ? 'selected' : '' }}>{{ __('5M - 6M VND') }}</option>
                <option value="6m_7m" {{ request('price_range') == '6m_7m' ? 'selected' : '' }}>{{ __('6M - 7M VND') }}</option>
                <option value="7m_8m" {{ request('price_range') == '7m_8m' ? 'selected' : '' }}>{{ __('7M - 8M VND') }}</option>
                <option value="8m_9m" {{ request('price_range') == '8m_9m' ? 'selected' : '' }}>{{ __('8M - 9M VND') }}</option>
                <option value="9m_10m" {{ request('price_range') == '9m_10m' ? 'selected' : '' }}>{{ __('9M - 10M VND') }}</option>
                <option value="over_10m" {{ request('price_range') == 'over_10m' ? 'selected' : '' }}>{{ __('Over 10M VND') }}</option>
            </select>
            
            <label>{{ __('Sort by') }}</label>
            <select class="sort-select" onchange="this.form.submit()" form="filter-form">
                <option value="" {{ !request('sort') ? 'selected' : '' }}>{{ __('Default') }}</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('Price Low-High') }}</option>
                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('Price High-Low') }}</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ __('Name A-Z') }}</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>{{ __('Name Z-A') }}</option>
            </select>
            
            <form id="filter-form" method="GET" action="{{ route('customer.products', $category->category_id) }}" style="display: none;">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <input type="hidden" name="sort" id="sort-input">
                <input type="hidden" name="price_range" id="price-input">
            </form>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="products-grid">
    @forelse($products as $product)
    <div class="product-card">
        <div class="product-image">
            <img src="{{ asset($product->image ?? 'images/default-product.svg') }}" alt="{{ $product->name }}">
            
            <!-- Product badges -->
            @if(!empty($product->discount_percentage))
                <span class="product-badge discount">-{{ $product->discount_percentage }}%</span>
            @endif
            @if(!empty($product->is_new))
                <span class="product-badge new">New</span>
            @endif
            @if($product->stock <= 0)
                <span class="product-badge out-of-stock">{{ __('Out of Stock') }}</span>
            @elseif($product->stock <= 5)
                <span class="product-badge low-stock">{{ __('Low Stock') }}</span>
            @endif
            
            <div class="product-overlay">
                @if($product->stock > 0)
                    <button class="add-to-cart-btn" data-url="{{ route('customer.cart.add', ['product' => $product]) }}">{{ __('Add to cart') }}</button>
                @else
                    <button class="add-to-cart-btn disabled" disabled>{{ __('Out of Stock') }}</button>
                @endif
                <div class="product-actions">
                    <a href="{{ route('customer.feedbacks', $product->product_id) }}" class="action-btn">
                        <i class="fas fa-comments"></i>
                        {{ __('Feedback') }}
                    </a>
                    <button class="action-btn">
                        <i class="fas fa-random"></i>
                        {{ __('Compare') }}
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-heart"></i>
                        {{ __('Like') }}
                    </button>
                </div>
            </div>
        </div>
        
        <div class="product-info">
            <h3>{{ $product->name }}</h3>
            <p class="product-desc">{{ $product->description }}</p>
            <div class="product-price">
                <span class="current-price">{{ number_format($product->price, 0, '.', ',') }} {{ __('VND') }}</span>
                @if($loop->index % 3 == 0)
                    <span class="original-price">{{ number_format($product->price * 1.3, 0, '.', ',') }} {{ __('VND') }}</span>
                @endif
            </div>
            <div class="stock-info">
                @if($product->stock > 0)
                    <span class="stock-available">{{ __('In Stock') }}: {{ $product->stock }}</span>
                @else
                    <span class="stock-out">{{ __('Out of Stock') }}</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="no-products">
        <p>{{ __('No products available in this category.') }}</p>
        <a href="{{ route('customer.categories') }}" class="back-btn">{{ __('Back to Categories') }}</a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div style="margin-top: 20px; display: flex; justify-content: center;">
    {{ $products->links('pagination.pagination') }}
</div>
@endsection

@push('styles')
<style>
/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(285px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.product-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.product-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    z-index: 2;
}

.product-badge.discount {
    background: #E97171;
    color: white;
}

.product-badge.new {
    background: #2EC1AC;
    color: white;
}

.product-badge.out-of-stock {
    background: #E74C3C;
    color: white;
}

.product-badge.low-stock {
    background: #F39C12;
    color: white;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(58, 58, 58, 0.72);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 15px;
    opacity: 0;
    transition: opacity 0.3s;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.add-to-cart-btn {
    background: #B88E2F;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.add-to-cart-btn:hover {
    background: #A67F2A;
}

.add-to-cart-btn.disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
}

.add-to-cart-btn.disabled:hover {
    background: #ccc;
}

.product-actions {
    display: flex;
    gap: 20px;
}

.action-btn {
    background: none;
    border: none;
    color: white;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s;
}

.action-btn:hover {
    color: #B88E2F;
}

.product-info {
    padding: 20px;
}

.product-info h3 {
    font-size: 20px;
    font-weight: 600;
    color: #3A3A3A;
    margin-bottom: 8px;
}

.product-desc {
    color: #898989;
    font-size: 14px;
    margin-bottom: 12px;
    line-height: 1.5;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 10px;
}

.current-price {
    font-size: 18px;
    font-weight: 600;
    color: #3A3A3A;
}

.original-price {
    font-size: 14px;
    color: #B0B0B0;
    text-decoration: line-through;
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
}

.no-products p {
    color: #666;
    font-size: 18px;
    margin-bottom: 20px;
}

.back-btn {
    background: #B88E2F;
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    transition: background 0.3s;
}

.back-btn:hover {
    background: #A67F2A;
}

/* Stock Info */
.stock-info {
    margin-top: 10px;
}

.stock-available {
    color: #27AE60;
    font-size: 12px;
    font-weight: 500;
}

.stock-out {
    color: #E74C3C;
    font-size: 12px;
    font-weight: 500;
}

/* Filter Bar */
.filter-bar {
    background: #F9F1E7;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    border-radius: 10px;
    margin: 20px 0;
}

.filter-left {
    display: flex;
    gap: 20px;
    align-items: center;
    flex: 1;
}

.search-form {
    flex: 1;
    max-width: 350px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    height: 48px;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: #666;
    z-index: 2;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 0 15px 0 40px;
    font-size: 14px;
    border-radius: 8px 0 0 8px;
    height: 100%;
    box-sizing: border-box;
}

.search-input::placeholder {
    color: #999;
}

.search-btn {
    background: #B88E2F;
    color: white;
    border: none;
    padding: 0 24px;
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s;
    height: 100%;
    min-width: 85px;
}

.search-btn:hover {
    background: #A67F2A;
}



.filter-right {
    display: flex;
    gap: 20px;
    align-items: center;
}

.showing-text {
    color: #666;
    font-size: 14px;
}

.show-select, .sort-select, .price-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    min-width: 120px;
}
</style>
@endpush

@section('scripts')
<script>
// Script for Add to Cart functionality
document.addEventListener('DOMContentLoaded', function () {
    // Get the CSRF token from the meta tag in the document head.
    // This token is essential for Laravel to protect against Cross-Site Request Forgery (CSRF) attacks.
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Select all buttons with the class 'add-to-cart-btn' that also have a 'data-url' attribute.
    // The 'data-url' attribute holds the URL where the cart addition request should be sent.
    document.querySelectorAll('.add-to-cart-btn[data-url]').forEach(function (btn) {
        // Attach a click event listener to each 'Add to cart' button.
        btn.addEventListener('click', function (e) {
            // Prevent the default form submission behavior of the button.
            e.preventDefault();

            // Get the URL for the cart addition request from the 'data-url' attribute.
            var url = btn.getAttribute('data-url');
            
            // If no URL is found, stop the function execution.
            if (!url) return;

            // Create a new HTML <form> element dynamically.
            var form = document.createElement('form');
            form.method = 'POST'; // Set the form method to POST.
            form.action = url;    // Set the form action URL.

            // Create a hidden input field for the CSRF token.
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token'; // Laravel expects the CSRF token in a field named '_token'.
            tokenInput.value = csrf;    // Assign the retrieved CSRF token value.
            form.appendChild(tokenInput); // Append the CSRF token input to the form.

            // Create a hidden input field for the quantity (defaulting to 1).
            var qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'quantity';
            qtyInput.value = '1';
            form.appendChild(qtyInput); // Append the quantity input to the form.

            // Append the dynamically created form to the document body.
            // This is necessary for the form to be submit-able by JavaScript.
            document.body.appendChild(form);

            // Submit the form programmatically.
            // This will trigger a POST request to the specified URL.
            form.submit();
            
            // Trigger cart shake animation
            if (typeof window.shakeCart === 'function') {
                // Small delay to ensure form submission starts first
                setTimeout(() => {
                    window.shakeCart();
                }, 100);
            }
        });
    });

    // Sort functionality
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const sortInput = document.getElementById('sort-input');
            if (sortInput) {
                sortInput.value = sortValue;
                document.getElementById('filter-form').submit();
            }
        });
    }

    // Price filter functionality
    const priceSelect = document.querySelector('.price-select');
    if (priceSelect) {
        priceSelect.addEventListener('change', function() {
            const priceValue = this.value;
            const priceInput = document.getElementById('price-input');
            if (priceInput) {
                priceInput.value = priceValue;
                document.getElementById('filter-form').submit();
            }
        });
    }

    // Search form auto-submit on Enter
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
});
</script>
@endsection
