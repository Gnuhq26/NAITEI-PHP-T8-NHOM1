@extends('customer.layouts.app')

@section('title', __('Orders') . ' - Furniro')

@section('hero')
<section class="hero-section">
    <div class="hero-content">
        <h1>{{ __('Orders') }}</h1>
    </div>
</section>
@endsection

@section('content')
<div class="orders-container">
    <!-- Tab Navigation -->
    <div class="order-tabs">
        <a href="{{ route('customer.orders', array_merge(request()->query(), ['tab' => 'active'])) }}" 
           class="tab-link {{ $tab === 'active' ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            {{ __('Active Orders') }}
        </a>
        <a href="{{ route('customer.orders', array_merge(request()->query(), ['tab' => 'history'])) }}" 
           class="tab-link {{ $tab === 'history' ? 'active' : '' }}">
            <i class="fas fa-history"></i>
            {{ __('Order History') }}
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('customer.orders') }}" class="filter-form">
            <input type="hidden" name="tab" value="{{ $tab }}">
            
            <div class="filter-group">
                <label for="status">{{ __('Status') }}:</label>
                <select id="status" name="status" class="status-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ __($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date_from">{{ __('From Date') }}:</label>
                <input type="date" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}" class="date-input">
            </div>
            
            <div class="filter-group">
                <label for="date_to">{{ __('To Date') }}:</label>
                <input type="date" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}" class="date-input">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i>
                    {{ __('Filter') }}
                </button>
                
                @if(request('date_from') || request('date_to') || request('status'))
                <a href="{{ route('customer.orders', ['tab' => $tab]) }}" class="btn-clear">
                    <i class="fas fa-times"></i>
                    {{ __('Clear') }}
                </a>
                @endif
            </div>
        </form>
    </div>

    @if($orders->count() > 0)
        <!-- Orders Count -->
        <div class="orders-count">
            <p>{{ __('Showing') }} {{ $orders->count() }} {{ __('of') }} {{ $orders->total() }} {{ __('orders') }}</p>
        </div>

        <!-- Orders List -->
        <div class="orders-list">
            @foreach($orders as $order)
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3 class="order-number">{{ __('Order') }} #{{ $order->order_id }}</h3>
                        <p class="order-date">{{ __('Order Date') }}: {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</p>
                    </div>
                    <div class="order-status">
                        @php
                            $statusClass = match($order->status ?? 'pending') {
                                'pending' => 'status-pending',
                                'approved' => 'status-approved',
                                'rejected' => 'status-rejected',
                                'delivering' => 'status-delivering',
                                'delivered' => 'status-delivered',
                                'cancelled' => 'status-cancelled',
                                default => 'status-pending'
                            };
                        @endphp
                        <span class="order-status-badge {{ $statusClass }}">
                            {{ __($order->status ?? 'pending') }}
                        </span>
                    </div>
                </div>

                <div class="order-body">
                    <div class="order-items">
                        @foreach($order->orderItems->take(3) as $item)
                        <div class="order-item">
                            <img src="{{ asset($item->product->image ?? 'images/default-product.svg') }}" 
                                 alt="{{ $item->product->name }}" class="item-image">
                            <div class="item-details">
                                <p class="item-name">{{ $item->product->name }}</p>
                                <p class="item-quantity">{{ __('Quantity') }}: {{ $item->quantity }}</p>
                            </div>
                            <div class="item-price">
                                <span class="price">{{ number_format($item->price, 0, ',', '.') }} {{ __('VND') }}</span>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($order->orderItems->count() > 3)
                        <div class="more-items">
                            <span>{{ __('and') }} {{ $order->orderItems->count() - 3 }} {{ __('more items') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="order-footer">
                    <div class="order-total">
                        <span class="total-label">{{ __('Total') }}:</span>
                        <span class="total-amount">{{ number_format($order->total_cost, 0, ',', '.') }} {{ __('VND') }}</span>
                    </div>
                    <div class="order-actions">
                        <a href="{{ route('customer.orders.details', $order->order_id) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i>
                            {{ __('View Details') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $orders->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="empty-orders">
            <div class="empty-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3>{{ $tab === 'active' ? __('No Active Orders') : __('No Order History') }}</h3>
            <p>
                @if($tab === 'active')
                    {{ __('You don\'t have any active orders. Start shopping to place your first order!') }}
                @else
                    {{ __('You don\'t have any completed orders yet.') }}
                @endif
            </p>
            @if($tab === 'active')
            <a href="{{ route('customer.categories') }}" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i>
                {{ __('Start Shopping') }}
            </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.orders-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.orders-header {
    text-align: center;
    margin-bottom: 2rem;
}

.orders-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
}

.orders-subtitle {
    color: #666;
    font-size: 1.1rem;
}

/* Tab Navigation */
.order-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #f0f0f0;
    justify-content: center;
}

.tab-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-link:hover {
    color: #B88E2F;
    background-color: rgba(184, 142, 47, 0.05);
}

.tab-link.active {
    color: #B88E2F;
    border-bottom-color: #B88E2F;
    background-color: rgba(184, 142, 47, 0.05);
}

/* Date Filter */
.filter-section {
    background: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.filter-form {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 1.5rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 500;
    color: #333;
    font-size: 0.9rem;
}

.date-input, .status-select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
    width: 100%;
    background: white;
    transition: all 0.3s ease;
    min-height: 48px;
    box-sizing: border-box;
}

.status-select {
    cursor: pointer;
}

.date-input:focus, .status-select:focus {
    outline: none;
    border-color: #B88E2F;
    box-shadow: 0 0 0 3px rgba(184, 142, 47, 0.1);
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    flex-direction: column;
}

.btn-filter, .btn-clear {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    height: auto;
    min-height: 48px;
}

.btn-filter {
    background: #B88E2F;
    color: white;
}

.btn-filter:hover {
    background: #a67c29;
}

.btn-clear {
    background: #6c757d;
    color: white;
}

.btn-clear:hover {
    background: #5a6268;
}

/* Orders Count */
.orders-count {
    margin-bottom: 1rem;
    color: #666;
    font-size: 0.9rem;
}

/* Order Cards */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.order-number {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 0.25rem 0;
}

.order-date {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.order-status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d1ecf1;
    color: #0c5460;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-delivering {
    background: #d1ecf1;
    color: #0c5460;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Order Items */
.order-items {
    margin-bottom: 1rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f8f8;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 500;
    color: #333;
    margin: 0 0 0.25rem 0;
    font-size: 0.95rem;
}

.item-quantity {
    color: #666;
    font-size: 0.85rem;
    margin: 0;
}

.item-price .price {
    font-weight: 600;
    color: #B88E2F;
    font-size: 0.95rem;
}

.more-items {
    text-align: center;
    padding: 0.5rem 0;
    color: #666;
    font-style: italic;
    font-size: 0.9rem;
}

/* Order Footer */
.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #f0f0f0;
}

.order-total {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.total-label {
    font-size: 1rem;
    color: #666;
}

.total-amount {
    font-size: 1.2rem;
    font-weight: 700;
    color: #B88E2F;
}

.order-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #B88E2F;
    color: white;
}

.btn-primary:hover {
    background: #a67c29;
    transform: translateY(-1px);
}

/* Empty State */
.empty-orders {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    border: 1px solid #e0e0e0;
}

.empty-icon {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 1.5rem;
}

.empty-orders h3 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 1rem;
}

.empty-orders p {
    color: #666;
    font-size: 1rem;
    margin-bottom: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .filter-form {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .filter-actions {
        grid-column: span 2;
        justify-content: center;
        flex-direction: row;
    }
}

@media (max-width: 768px) {
    .orders-container {
        padding: 1rem;
    }
    
    .order-tabs {
        flex-direction: column;
        gap: 0;
    }
    
    .tab-link {
        border-bottom: 1px solid #f0f0f0;
        border-radius: 0;
    }
    
    .filter-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .filter-actions {
        flex-direction: row;
        justify-content: center;
    }
    
    .order-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .order-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .order-actions {
        justify-content: center;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .item-details {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .orders-header h2 {
        font-size: 2rem;
    }
    
    .order-card {
        padding: 1rem;
    }
    
    .item-image {
        width: 50px;
        height: 50px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Set max date for date inputs to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput) {
        dateFromInput.setAttribute('max', today);
    }
    
    if (dateToInput) {
        dateToInput.setAttribute('max', today);
    }
    
    // Validate date range
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            dateToInput.setAttribute('min', this.value);
        });
        
        dateToInput.addEventListener('change', function() {
            dateFromInput.setAttribute('max', this.value);
        });
    }
});
</script>
@endpush
