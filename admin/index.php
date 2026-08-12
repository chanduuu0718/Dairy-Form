<?php
/**
 * Admin Dashboard
 */
$adminTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Dashboard-specific stats (todayOrders, activeSubs, totalCustomers, unreadContacts come from header.php)
$pendingOrders = $db->fetchOne("SELECT COUNT(*) as c FROM orders WHERE order_status = 'pending'");
$totalRevenue = $db->fetchOne("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE payment_status = 'paid'");
$monthRevenue = $db->fetchOne("SELECT COALESCE(SUM(total_amount),0) as t FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURDATE())");
$pendingVisits = $db->fetchOne("SELECT COUNT(*) as c FROM farm_visits WHERE status = 'pending'");
$recentOrders = $db->fetchAll(
    "SELECT o.*, u.name as user_name, u.phone as user_phone FROM orders o
     JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10"
);
?>

<h1 class="admin-page-title">Dashboard</h1>

<!-- Stats Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card stat-blue">
        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $todayOrders['c'] ?></span>
            <span class="stat-label">Today's Orders</span>
        </div>
    </div>
    <div class="admin-stat-card stat-green">
        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
            <span class="stat-number">₹<?= number_format($todayOrders['t'], 0) ?></span>
            <span class="stat-label">Today's Revenue</span>
        </div>
    </div>
    <div class="admin-stat-card stat-purple">
        <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-info">
            <span class="stat-number">₹<?= number_format($monthRevenue['t'], 0) ?></span>
            <span class="stat-label">Monthly Revenue</span>
        </div>
    </div>
    <div class="admin-stat-card stat-orange">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $pendingOrders['c'] ?></span>
            <span class="stat-label">Pending Orders</span>
        </div>
    </div>
    <div class="admin-stat-card stat-teal">
        <div class="stat-icon"><i class="fas fa-sync"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $activeSubs['c'] ?></span>
            <span class="stat-label">Active Subscriptions</span>
        </div>
    </div>
    <div class="admin-stat-card stat-pink">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $totalCustomers['c'] ?></span>
            <span class="stat-label">Total Customers</span>
        </div>
    </div>
    <div class="admin-stat-card stat-yellow">
        <div class="stat-icon"><i class="fas fa-tractor"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $pendingVisits['c'] ?></span>
            <span class="stat-label">Pending Visits</span>
        </div>
    </div>
    <div class="admin-stat-card stat-red">
        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?= $unreadContacts['c'] ?></span>
            <span class="stat-label">Unread Messages</span>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="admin-card mt-4">
    <div class="admin-card-header">
        <h2>Recent Orders</h2>
        <a href="<?= SITE_URL ?>/admin/pages/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="7" class="text-center">No orders yet</td></tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?= $order['order_number'] ?></strong></td>
                            <td>
                                <div><?= htmlspecialchars($order['user_name']) ?></div>
                                <small class="text-muted"><?= $order['user_phone'] ?></small>
                            </td>
                            <td><?= formatPrice($order['total_amount']) ?></td>
                            <td>
                                <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst($order['payment_status']) ?>
                                </span>
                            </td>
                            <td><?= getOrderStatusBadge($order['order_status']) ?></td>
                            <td><?= date('d M, h:i A', strtotime($order['created_at'])) ?></td>
                            <td>
                                <select class="form-input form-select form-select-sm order-status-select" data-id="<?= $order['id'] ?>">
                                    <?php foreach (['pending','confirmed','processing','out_for_delivery','delivered','cancelled'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>>
                                            <?= ucwords(str_replace('_', ' ', $s)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update order status
    $('.order-status-select').on('change', function() {
        const orderId = $(this).data('id');
        const status = $(this).val();

        $.ajax({
            url: SITE_URL + '/api/orders/?action=update-status',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ order_id: orderId, status: status }),
            success: function(res) {
                showToast(res.message, res.success ? 'success' : 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
