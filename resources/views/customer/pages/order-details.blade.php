@extends('customer.layouts.app')

@section('title', __('Order Details') . ' #' . $order->order_id . ' - Furniro')

@section('hero')
<section class="hero-section">
    <div class="hero-content">
        <h1>{{ __('Order Details') }} #{{ $order->order_id }}</h1>
        <div class="breadcrumb">
            <a href="{{ route('customer.categories') }}">{{ __('Home') }}</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('customer.orders') }}">{{ __('Orders') }}</a>
            <i class="fas fa-chevron-right"></i>
            <span>{{ __('Order Details') }}</span>
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="order-details-container">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Order Header Info -->
    <div class="order-info-section">
        <div class="order-info-card">
            <div class="order-basic-info">
                <h2>{{ __('Order') }} #{{ $order->order_id }}</h2>
                <div class="order-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>{{ __('Order Date') }}: {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span>{{ __('Customer') }}: {{ $order->user->name }}</span>
                    </div>
                </div>
            </div>
            
            <div class="order-status-section">
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
                <span class="current-status {{ $statusClass }}">
                    {{ __($order->status ?? 'pending') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Order Status Timeline -->
    @if($order->statusOrders->count() > 0)
    <div class="status-timeline-section">
        <h3>{{ __('Order Status Timeline') }}</h3>
        <div class="timeline">
            @foreach($order->statusOrders->sortBy('date') as $statusOrder)
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h4>{{ __($statusOrder->action_type) }}</h4>
                    <p class="timeline-date">{{ \Carbon\Carbon::parse($statusOrder->date)->format('d/m/Y H:i') }}</p>
                    @if($statusOrder->admin)
                        <p class="timeline-admin">{{ __('Updated by') }}: {{ $statusOrder->admin->name }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Order Items -->
    <div class="order-items-section">
        <h3>{{ __('Order Items') }}</h3>
        <div class="items-table">
            <div class="table-header">
                <div class="col-product">{{ __('Product') }}</div>
                <div class="col-price">{{ __('Unit Price') }}</div>
                <div class="col-quantity">{{ __('Quantity') }}</div>
                <div class="col-subtotal">{{ __('Subtotal') }}</div>
            </div>
            
            @foreach($order->orderItems as $item)
            <div class="table-row">
                <div class="col-product">
                    <img src="{{ asset($item->product->image ?? 'images/default-product.svg') }}" alt="{{ $item->product->name }}">
                    <div class="product-info">
                        <h4>{{ $item->product->name }}</h4>
                        <p>{{ $item->product->description }}</p>
                    </div>
                </div>
                <div class="col-price">{{ number_format($item->price, 0, '.', ',') }} {{ __('VND') }}</div>
                <div class="col-quantity">{{ $item->quantity }}</div>
                <div class="col-subtotal">{{ number_format($item->price * $item->quantity, 0, '.', ',') }} {{ __('VND') }}</div>
            </div>
            @endforeach
        </div>
        
        <!-- Order Summary -->
        <div class="order-summary-section">
            <div class="summary-row">
                <span>{{ __('Items Total') }}</span>
                <span>{{ number_format($order->orderItems->sum(function($item) { return $item->price * $item->quantity; }), 0, '.', ',') }} {{ __('VND') }}</span>
            </div>
            <div class="summary-row">
                <span>{{ __('Shipping') }}</span>
                <span>{{ __('Free') }}</span>
            </div>
            <div class="summary-row total">
                <span>{{ __('Total') }}</span>
                <span>{{ number_format($order->total_cost, 0, '.', ',') }} {{ __('VND') }}</span>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="order-actions-section">
        <a href="{{ route('customer.orders') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            {{ __('Back to Orders') }}
        </a>
        
        @if($order->status === 'delivered')
        <button class="btn-reorder">
            <i class="fas fa-redo"></i>
            {{ __('Reorder Items') }}
        </button>
        @endif
        
        @if($order->status === 'pending')
        <button class="btn-cancel" onclick="confirmCancel()">
            <i class="fas fa-times"></i>
            {{ __('Cancel Order') }}
        </button>
        @endif
        
        @if($order->status === 'cancelled')
        <div class="order-cancelled-notice">
            <i class="fas fa-exclamation-circle"></i>
            {{ __('This order has been cancelled') }}
        </div>
        @endif
    </div>
</div>

<!-- Cancel Order Modal -->
<div id="cancelModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3>{{ __('Cancel Order') }}</h3>
        <p>{{ __('Are you sure you want to cancel this order?') }}</p>
        <div class="modal-actions">
            <button onclick="closeCancelModal()" class="btn-secondary">{{ __('No, Keep Order') }}</button>
            <button onclick="cancelOrder()" class="btn-danger">{{ __('Yes, Cancel Order') }}</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.order-details-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 16px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Order Info Section */
.order-info-section {
    margin-bottom: 40px;
}

.order-info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 30px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.order-basic-info h2 {
    font-size: 28px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.order-meta {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #666;
    font-size: 14px;
}

.meta-item i {
    color: #B88E2F;
    width: 16px;
}

/* Status */
.current-status {
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 14px;
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

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.status-delivering {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Status Timeline */
.status-timeline-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 40px;
}

.status-timeline-section h3 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    background: #B88E2F;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #B88E2F;
}

.timeline-content h4 {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    text-transform: capitalize;
}

.timeline-date {
    font-size: 14px;
    color: #666;
    margin-bottom: 3px;
}

.timeline-admin {
    font-size: 12px;
    color: #999;
}

/* Order Items Section */
.order-items-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 40px;
}

.order-items-section h3 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
}

/* Items Table */
.items-table {
    margin-bottom: 30px;
}

.table-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 20px;
    padding: 15px 0;
    border-bottom: 2px solid #f0f0f0;
    font-weight: 600;
    color: #333;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.col-product {
    display: flex;
    align-items: center;
    gap: 15px;
}

.col-product img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.product-info h4 {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.product-info p {
    font-size: 14px;
    color: #666;
    line-height: 1.4;
}

.col-price,
.col-quantity,
.col-subtotal {
    font-size: 16px;
    color: #333;
    text-align: center;
}

/* Order Summary */
.order-summary-section {
    max-width: 400px;
    margin-left: auto;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    font-size: 16px;
}

.summary-row.total {
    border-top: 1px solid #f0f0f0;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 20px;
    font-weight: 600;
    color: #B88E2F;
}

/* Actions Section */
.order-actions-section {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-back,
.btn-reorder,
.btn-cancel {
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-back {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.btn-back:hover {
    background: #e9ecef;
}

.btn-reorder {
    background: #B88E2F;
    color: white;
}

.btn-reorder:hover {
    background: #A67F2A;
}

.btn-cancel {
    background: #dc3545;
    color: white;
}

.btn-cancel:hover {
    background: #c82333;
}

.order-cancelled-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #f8d7da;
    color: #721c24;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.modal-content h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 15px;
}

.modal-content p {
    color: #666;
    margin-bottom: 25px;
}

.modal-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-secondary,
.btn-danger {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .order-info-card {
        flex-direction: column;
        gap: 20px;
    }
    
    .table-header,
    .table-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .col-product {
        flex-direction: column;
        text-align: center;
    }
    
    .order-actions-section {
        flex-direction: column;
        align-items: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
let isProcessing = false;

function confirmCancel() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

function cancelOrder() {
    if (isProcessing) return;
    isProcessing = true;
    
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        alert('{{ __("Security token not found. Please refresh the page.") }}');
        isProcessing = false;
        return;
    }

    const cancelButton = document.querySelector('.btn-danger');
    const originalText = cancelButton.textContent;
    cancelButton.textContent = '{{ __("Cancelling...") }}';
    cancelButton.disabled = true;

    const formData = new FormData();
    formData.append('_token', token);

    fetch(`{{ route('customer.orders.cancel', $order->order_id) }}`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || '{{ __("An error occurred while cancelling the order!") }}');
        }
    })
    .catch(error => {
        alert('{{ __("An error occurred while cancelling the order!") }}');
    })
    .finally(() => {
        cancelButton.textContent = originalText;
        cancelButton.disabled = false;
        isProcessing = false;
        closeCancelModal();
    });
}
</script>
@endpush
