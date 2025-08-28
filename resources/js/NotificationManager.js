import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

class NotificationManager {
    constructor(adminId) {
        console.log('NotificationManager: Initializing...');
        this.adminId = adminId;
        this.notificationCountElement = document.querySelector('.notification-count');
        this.notificationListElement = document.querySelector('.notification-list');
        this.notificationBell = document.querySelector('.notification-bell');
        this.noNotificationsMessage = this.notificationListElement.querySelector('.no-notifications');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // No need to call initEcho() here. Assume window.Echo is initialized by bootstrap.js
        // this.initEcho(); 
        
        // Wait for Echo to be ready before setting up listeners
        // This is a common pattern when Echo is initialized externally
        if (window.Echo) {
            this.setupEchoListeners();
        } else {
            // Fallback or a more robust way to wait for Echo if it's not immediately available
            console.warn('NotificationManager: window.Echo not found. Real-time notifications might not work.');
            // You might want to implement a retry mechanism or ensure bootstrap.js loads first.
        }

        this.addEventListeners();
        this.fetchNotifications(); // This should trigger API calls on init
        this.startPolling();
    }

    initEcho() {
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
            wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
            wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
            wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo.channel('admin.orders')
            .listen('.new-order-placed', (e) => {
                console.log('Real-time new order received:', e);
                this.showToastNotification(`New Order #${e.order_id} from ${e.customer_name}!`);
                this.fetchNotifications(); // Refresh notifications and count
            });
    }

    setupEchoListeners() {
        window.Echo.channel('admin.orders')
            .listen('.new-order-placed', (e) => {
                console.log('Real-time new order received:', e);
                this.showToastNotification(`New Order #${e.order_id} from ${e.customer_name}!`);
                this.fetchNotifications(); // Refresh notifications and count
            });
    }

    addEventListeners() {
        this.notificationBell.addEventListener('click', () => {
            this.notificationListElement.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (!this.notificationBell.contains(event.target) && !this.notificationListElement.contains(event.target)) {
                this.notificationListElement.classList.remove('active');
            }
        });
    }

    async fetchNotifications() {
        console.log('NotificationManager: Fetching notifications...');
        try {
            const response = await fetch(window.Laravel.routes.notificationsRecent, { // Using named route
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            const notifications = await response.json();
            console.log('NotificationManager: Received recent notifications:', notifications);
            // Fetch unread count separately as getRecent only fetches recent notifications
            const countResponse = await fetch(window.Laravel.routes.notificationsCount, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            const countData = await countResponse.json();
            console.log('NotificationManager: Received unread count:', countData.unread_count);

            this.updateNotificationUI(notifications);
            this.updateNotificationCount(countData.unread_count || 0);
        } catch (error) {
            console.error('NotificationManager: Error fetching notifications:', error);
        }
    }

    updateNotificationUI(notifications) {
        this.notificationListElement.innerHTML = ''; // Clear existing notifications

        if (notifications.length === 0) {
            this.notificationListElement.innerHTML = `<div class="dropdown-item no-notifications">${window.Laravel.translations.noNewNotifications || 'No new notifications'}</div>`;
            return;
        }

        notifications.forEach(notification => {
            const notificationItem = document.createElement('a');
            // Construct URL to specific order details page
            if (notification.related_id && window.Laravel.routes.ordersDetails) {
                notificationItem.href = `${window.Laravel.routes.ordersDetails}/${notification.related_id}/details`;
            } else {
                notificationItem.href = window.Laravel.routes.notificationsIndex; // Fallback to general notifications page
            }
            notificationItem.classList.add('dropdown-item');
            if (!notification.is_read) {
                notificationItem.classList.add('unread');
            }
            notificationItem.innerHTML = `
                <strong>${notification.title}</strong><br/>
                <span>${notification.message}</span><br/>
                <small>${new Date(notification.created_at).toLocaleString()}</small>
            `;
            notificationItem.addEventListener('click', async (event) => {
                event.preventDefault(); // Prevent default link behavior
                await this.markAsRead(notification.notification_id, notificationItem);
                
                // Open the order details modal
                if (notification.related_id && window.adminPanel && typeof window.adminPanel.openModal === 'function') {
                    // Show loading state immediately in the modal
                    const itemsList = document.getElementById('order_detail_items_list');
                    if (itemsList) {
                        itemsList.innerHTML = `
                            <div class="order-items-loading">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p style="margin-top: 10px;">${window.Laravel.translations.loadingOrderDetails || 'Loading order details...'}</p>
                            </div>
                        `;
                    }

                    window.adminPanel.openModal('viewOrderDetailsModal');

                    // Fetch and populate order details, similar to orders.blade.php
                    fetch(`${window.Laravel.routes.ordersDetails}/${notification.related_id}/details`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Order details received from notification click:', data);
                        const order = data.order;
                        const customerName = data.customer_name;
                        const orderItems = data.order_items;
                        const statusHistory = data.status_history; // Although not used here, good to fetch
                        
                        // Populate general order info
                        document.getElementById('order_detail_id').value = `#${order.order_id}`;
                        document.getElementById('order_detail_customer_name').value = customerName;
                        document.getElementById('order_detail_date').value = new Date(order.order_date).toLocaleDateString();
                        document.getElementById('order_detail_shipping_fee').value = `${new Intl.NumberFormat('vi-VN').format(order.shipping_fee)} VNĐ`; // Modified line
                        document.getElementById('order_detail_total_amount').value = `${new Intl.NumberFormat('vi-VN').format(order.total_cost)} VNĐ`;
                        // Use translations from window.Laravel
                        const ucfirst = (str) => { if (!str) return ''; return str.charAt(0).toUpperCase() + str.slice(1); };
                        document.getElementById('order_detail_status').value = ucfirst(window.Laravel.translations[order.status] || 'Unknown Status');

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
                                    <p>${window.Laravel.translations.noItemsFound || 'No items found in this order.'}</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('NotificationManager: Error fetching order details from notification:', error);

                        const itemsList = document.getElementById('order_detail_items_list');
                        if (itemsList) {
                            itemsList.innerHTML = `
                                <div class="order-items-empty">
                                    <i class="fas fa-exclamation-triangle fa-3x" style="margin-bottom: 15px; color: #dc3545;"></i>
                                    <p style="color: #dc3545;">${window.Laravel.translations.failedToLoadOrderDetails || 'Failed to load order details.'}</p>
                                    <button onclick="location.reload()" class="btn btn-secondary btn-sm" style="margin-top: 10px;">
                                        ${window.Laravel.translations.retry || 'Retry'}
                                    </button>
                                </div>
                            `;
                        }
                    });

                } else {
                    console.warn('NotificationManager: adminPanel.openModal or related_id not available. Falling back to general notifications page.');
                    window.location.href = window.Laravel.routes.notificationsIndex; // Fallback
                }
            });
            this.notificationListElement.appendChild(notificationItem);
        });
    }

    updateNotificationCount(count) {
        this.notificationCountElement.textContent = count;
        if (count > 0) {
            this.notificationCountElement.style.display = 'block';
        } else {
            this.notificationCountElement.style.display = 'none';
        }
    }

    async markAsRead(notificationId, element) {
        try {
            const response = await fetch(`${window.Laravel.routes.notificationsMarkAsRead}/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.message === 'Notification marked as read.') {
                element.classList.remove('unread');
                this.fetchNotifications(); // Refresh count
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    startPolling() {
        this.pollingInterval = setInterval(() => {
            this.fetchNotifications();
        }, 30000); // Poll every 30 seconds
    }

    showToastNotification(message) {
        // This is a basic example. You might want to use a dedicated toast library (e.g., ToastifyJS, SweetAlert2)
        const toast = document.createElement('div');
        toast.classList.add('toast-notification');
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000); // Toast disappears after 5 seconds
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
}

export default NotificationManager; 