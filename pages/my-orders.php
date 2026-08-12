<?php
/**
 * My Orders Page
 */
require_once __DIR__ . '/../config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user = Auth::getUser();
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">My Orders</span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="account-layout">
            <aside class="account-sidebar">
                <div class="account-user-card">
                    <div class="avatar-placeholder avatar-lg"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <h3><?= htmlspecialchars($user['name']) ?></h3>
                    <p><?= htmlspecialchars($user['phone']) ?></p>
                </div>
                <nav class="account-nav">
                    <a href="<?= SITE_URL ?>/pages/profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="<?= SITE_URL ?>/pages/my-orders.php" class="active"><i class="fas fa-box"></i> My Orders</a>
                    <a href="<?= SITE_URL ?>/pages/my-subscriptions.php"><i class="fas fa-sync"></i> Subscriptions</a>
                    <a href="#" onclick="PMAuth.logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>

            <div class="account-main">
                <div class="account-card">
                    <h2><i class="fas fa-box"></i> My Orders</h2>
                    <div id="orders-container">
                        <div class="loading-spinner"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    let currentPage = 1;

    function loadOrders(page) {
        $.get(SITE_URL + '/api/orders/?page=' + page, function(res) {
            if (!res.success || !res.orders.length) {
                $('#orders-container').html(`
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No orders yet</h3>
                        <p>Place your first order for farm-fresh products!</p>
                        <a href="${SITE_URL}/pages/products.php" class="btn btn-primary">Shop Now</a>
                    </div>
                `);
                return;
            }

            let html = '';
            res.orders.forEach(function(order) {
                const statusMap = {
                    pending: 'badge-warning',
                    confirmed: 'badge-info',
                    processing: 'badge-primary',
                    out_for_delivery: 'badge-accent',
                    delivered: 'badge-success',
                    cancelled: 'badge-danger'
                };
                const statusClass = statusMap[order.order_status] || '';

                let itemsList = '';
                order.items.forEach(function(item) {
                    itemsList += `<span class="order-product-name">${item.product_name} x ${item.quantity}</span>`;
                });

                html += `
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <strong>Order #${order.order_number}</strong>
                                <span class="order-date">${new Date(order.created_at).toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'})}</span>
                            </div>
                            <span class="badge ${statusClass}">${order.order_status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                        </div>
                        <div class="order-card-items">${itemsList}</div>
                        <div class="order-card-footer">
                            <span class="order-total"><strong>₹${parseFloat(order.total_amount).toFixed(2)}</strong></span>
                            <span class="order-payment">${order.payment_method === 'cod' ? 'Cash on Delivery' : 'Paid Online'}</span>
                            <a href="${SITE_URL}/pages/order-confirmation.php?id=${order.id}" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                `;
            });

            $('#orders-container').html(html);
        });
    }

    loadOrders(currentPage);
});
</script>
