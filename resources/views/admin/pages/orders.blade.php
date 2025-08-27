@extends('admin.layouts.app')

@section('content')
<div class="content-section active">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 0 20px 20px 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 0 20px 20px 20px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 0 20px 20px 20px;">
            <i class="fas fa-exclamation-triangle"></i>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">{{ __('Order Management') }}</h2>
        </div>
        <div style="padding: 20px; border-bottom: 1px solid #eee;">
            <form method="GET" action="{{ route('admin.orders') }}" id="orderFilterForm" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                <div style="display: flex; flex-direction: column;">
                    <label style="margin-bottom: 5px; font-weight: 500;">{{ __('Status') }}</label>
                    <select name="status" class="form-control" style="width: 160px;">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                        <option value="delivering" {{ request('status') == 'delivering' ? 'selected' : '' }}>{{ __('Delivering') }}</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>{{ __('Delivered') }}</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column;">
                    <label style="margin-bottom: 5px; font-weight: 500;">{{ __('From Date') }}</label>
                    <input type="date" name="from_date" class="form-control" style="width: 150px;" value="{{ request('from_date') }}">
                </div>
                
                <div style="display: flex; flex-direction: column;">
                    <label style="margin-bottom: 5px; font-weight: 500;">{{ __('To Date') }}</label>
                    <input type="date" name="to_date" class="form-control" style="width: 150px;" value="{{ request('to_date') }}">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.orders') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> {{ __('Clear') }}
                    </a>
                </div>
            </form>
        </div>

        @if(request()->hasAny(['status', 'from_date', 'to_date']))
        <div style="padding: 10px 20px; background-color: #f8f9fa; border-bottom: 1px solid #eee;">
            <small class="text-muted">
                {{ __('Filtered results') }}: {{ $orders->count() }} {{ __('orders found') }}
                @if(request('status'))
                    | {{ __('Status') }}: <strong>{{ __(ucfirst(request('status'))) }}</strong>
                @endif
                @if(request('from_date'))
                    | {{ __('From') }}: <strong>{{ \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') }}</strong>
                @endif
                @if(request('to_date'))
                    | {{ __('To') }}: <strong>{{ \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') }}</strong>
                @endif
            </small>
        </div>
        @endif
        
        <div style="padding: 20px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Order ID') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Order Date') }}</th>
                        <th>{{ __('Total Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->order_id }}</td>
                        <td>{{ $order->user->user_name ?? $order->user->name ?? __('N/A') }}</td>
                        <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') : __('N/A') }}</td>
                        <td>{{ number_format($order->total_cost) }} {{ __('VNĐ') }}</td>
                        <td>
                            @php
                                $status = $order->status ?? 'pending';
                                $statusClasses = [
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-reject',
                                    'delivering' => 'status-delivering',
                                    'delivered' => 'status-delivered',
                                    'cancelled' => 'status-cancelled'
                                ];
                                $statusTexts = [
                                    'pending' => __('Pending'),
                                    'approved' => __('Approved'),
                                    'rejected' => __('Rejected'),
                                    'delivering' => __('Delivering'),
                                    'delivered' => __('Delivered'),
                                    'cancelled' => __('Cancelled')
                                ];
                                $statusClass = $statusClasses[$status] ?? 'status-pending';
                                $statusText = $statusTexts[$status] ?? __('Pending');
                            @endphp
                            <span class="status-badge order-status-badge {{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.orders.status.update', $order->order_id) }}" method="POST" style="display:inline-flex; gap:8px; align-items:center;" class="order-status-form" data-current-status="{{ $status }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-control order-status-select" style="width:auto;" 
                                        {{ in_array($status, ['delivered', 'cancelled', 'rejected']) ? 'disabled' : '' }}>
                                    @if($status === 'pending')
                                        <option value="pending" selected>{{ __('Pending') }}</option>
                                        <option value="approved">{{ __('Approve') }}</option>
                                        <option value="rejected">{{ __('Reject') }}</option>
                                    @elseif($status === 'approved')
                                        <option value="approved" selected>{{ __('Approved') }}</option>
                                        <option value="delivering">{{ __('Delivering') }}</option>
                                    @elseif($status === 'delivering')
                                        <option value="delivering" selected>{{ __('Delivering') }}</option>
                                        <option value="delivered">{{ __('Delivered') }}</option>
                                    @else
                                        {{-- rejected, delivered, cancelled -> show current status only --}}
                                        <option value="{{ $status }}" selected>{{ $statusText }}</option>
                                    @endif
                                </select>
                                <button type="submit" class="btn btn-success btn-sm order-status-submit" 
                                        {{ in_array($status, ['delivered', 'cancelled', 'rejected']) ? 'disabled' : '' }}>
                                    <i class="fas fa-save"></i> {{ __('Update') }}
                                </button>
                            </form>
                                <button class="btn btn-secondary btn-sm view-order-details-btn" data-order-id="{{ $order->order_id }}">
                                    <i class="fas fa-eye"></i> {{ __('View Details') }}
                                </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">{{ __('No orders found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                {{ $orders->links('pagination.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Translations are injected from the backend using Blade's __() helper.
    // This approach makes localized strings available in JavaScript without a dedicated localization library or API.
    // If you need to support more languages or dynamic translations, consider using a standard localization solution.
    const translations = {
        approved: "{{ __('Approved') }}",
        pending: "{{ __('Pending') }}",
        rejected: "{{ __('Rejected') }}",
        delivering: "{{ __('Delivering') }}",
        delivered: "{{ __('Delivered') }}"
    };

    function ucfirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const statusSelect = document.querySelector('select[name="status"]');
        const fromDateInput = document.querySelector('input[name="from_date"]');
        const toDateInput = document.querySelector('input[name="to_date"]');
        
        // auto-submit when status changes
        statusSelect.addEventListener('change', () => {
            document.getElementById('orderFilterForm').submit();
        });
        
        // auto-submit when from date changes
        fromDateInput.addEventListener('change', function() {
            if (toDateInput.value && this.value > toDateInput.value) {
                dateErrorMsg.textContent = '{{ __('From date cannot be later than To date') }}';
                dateErrorMsg.style.display = 'block';
                this.value = '';
                return;
            } else {
                dateErrorMsg.textContent = '';
                dateErrorMsg.style.display = 'none';
            }
            // auto-submit if date is valid
            if (this.value) {
                document.getElementById('orderFilterForm').submit();
            }
        });
        
        // auto-submit when to date changes
        toDateInput.addEventListener('change', function() {
            if (fromDateInput.value && this.value < fromDateInput.value) {
                alert('{{ __('To date cannot be earlier than From date') }}');
                this.value = '';
                return;
            }
            // auto-submit if date is valid
            if (this.value) {
                document.getElementById('orderFilterForm').submit();
            }
        });

        // Handle View Details button click
        document.querySelectorAll('.view-order-details-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const orderId = e.currentTarget.getAttribute('data-order-id');
                if (!orderId) {
                    console.error('Order ID not found for view details button.');
                    return;
                }
                
                // Show loading state immediately
                const itemsList = document.getElementById('order_detail_items_list');
                itemsList.innerHTML = `
                    <div class="order-items-loading">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p style="margin-top: 10px;">{{ __('Loading order details...') }}</p>
                    </div>
                `;
                
                // Open modal first to show loading
                adminPanel.openModal('viewOrderDetailsModal');
                
                fetch(`/admin/orders/${orderId}/details`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Order details received:', data);
                    const order = data.order;
                    const customerName = data.customer_name;
                    const orderItems = data.order_items;
                    const statusHistory = data.status_history;
        
                    // Populate general order info
                    document.getElementById('order_detail_id').value = `#${order.order_id}`;
                    document.getElementById('order_detail_customer_name').value = customerName;
                    document.getElementById('order_detail_date').value = new Date(order.order_date).toLocaleDateString();
                    document.getElementById('order_detail_total_amount').value = `${order.total_cost.toLocaleString()} VNĐ`;
                    document.getElementById('order_detail_status').value = ucfirst(translations[order.status] || '{{ __("Unknown Status") }}'); 

                    // Populate order items list
                    const itemsList = document.getElementById('order_detail_items_list');
                    itemsList.innerHTML = ''; // Clear previous items
                    orderItems.forEach((item, index) => {
                        const itemCard = `
                            <div class="order-item-card" style="
                                background: white; 
                                margin-bottom: 1px; 
                                padding: 16px 20px; 
                                display: flex; 
                                align-items: center; 
                                justify-content: space-between;
                                border-left: 4px solid #007bff;
                                transition: all 0.2s ease;
                            " onmouseover="this.style.backgroundColor='#f1f3f5'" onmouseout="this.style.backgroundColor='white'">
                                <div style="display: flex; align-items: center; flex: 1;">
                                    <div style="
                                        background: linear-gradient(135deg, #007bff, #0056b3); 
                                        color: white; 
                                        width: 32px; 
                                        height: 32px; 
                                        border-radius: 50%; 
                                        display: flex; 
                                        align-items: center; 
                                        justify-content: center; 
                                        font-weight: bold; 
                                        margin-right: 15px;
                                        font-size: 14px;
                                    ">${index + 1}</div>
                                    <div>
                                        <div style="font-weight: 600; color: #333; font-size: 16px; margin-bottom: 2px;">
                                            ${item.product_name}
                                        </div>
                                        <div style="color: #666; font-size: 13px;">
                                            <i class="fas fa-tag" style="margin-right: 4px;"></i>
                                            ${item.price.toLocaleString()} VNĐ × ${item.quantity}
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="
                                        background: #28a745; 
                                        color: white; 
                                        padding: 6px 12px; 
                                        border-radius: 20px; 
                                        font-weight: 600; 
                                        font-size: 14px;
                                        display: inline-block;
                                    ">
                                        ${item.subtotal.toLocaleString()} VNĐ
                                    </div>
                                </div>
                            </div>
                        `;
                        itemsList.insertAdjacentHTML('beforeend', itemCard);
                    });
                    
                    // Add empty state if no items
                    if (orderItems.length === 0) {
                        itemsList.innerHTML = `
                            <div class="order-items-empty">
                                <i class="fas fa-box-open fa-3x" style="margin-bottom: 15px; color: #dee2e6;"></i>
                                <p>{{ __('No items found in this order.') }}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    
                    const itemsList = document.getElementById('order_detail_items_list');
                    itemsList.innerHTML = `
                        <div class="order-items-empty">
                            <i class="fas fa-exclamation-triangle fa-3x" style="margin-bottom: 15px; color: #dc3545;"></i>
                            <p style="color: #dc3545;">{{ __('Failed to load order details.') }}</p>
                            <button onclick="location.reload()" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                                {{ __('Retry') }}
                            </button>
                        </div>
                    `;
                });
            });
        });

        // handle order status form submissions
        document.querySelectorAll('.order-status-form').forEach(form => {
            const submitButton = form.querySelector('.order-status-submit');
            const statusSelect = form.querySelector('.order-status-select');
            const currentStatus = form.getAttribute('data-current-status');
            
            if (submitButton) {
                submitButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    const newStatus = statusSelect.value;
                    
                    // if status is not changing -> do nothing
                    if (currentStatus === newStatus) {
                        return;
                    }
                    
                    // Show warning for final status transitions
                    if (['delivered'].includes(newStatus)) {
                        adminPanel.openModal('orderStatusWarningModal');
                        
                        const confirmBtn = document.getElementById('confirmStatusChangeBtn');
                        confirmBtn.onclick = function() {
                            adminPanel.closeModal('orderStatusWarningModal');
                            form.submit();
                        };
                        return;
                    }
                    
                    form.submit();
                });
            }
        });

        function initAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.classList.add('auto-hide');
                
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            });

            document.querySelectorAll('.alert .btn-close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.style.animation = 'slideOutUp 0.3s ease';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                });
            });
        }

        initAlerts();
    });
</script>
@endsection
