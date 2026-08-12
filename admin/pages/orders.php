<?php
/**
 * Admin: Orders Management
 */
$adminTitle = 'Orders';

// Mark all current orders as "read" by recording this visit timestamp
// Must be set BEFORE header.php loads so the badge query sees it immediately
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['orders_last_viewed'] = date('Y-m-d H:i:s');

require_once __DIR__ . '/../includes/header.php';

// Get filter values
$status = sanitize($_GET['status'] ?? '');
$dateFrom = sanitize($_GET['date_from'] ?? '');
$dateTo = sanitize($_GET['date_to'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

// Build query
$where = "1=1";
$params = [];
$types = '';

if (!empty($status)) {
    $where .= " AND o.order_status = ?";
    $params[] = $status;
    $types .= 's';
}
if (!empty($dateFrom)) {
    $where .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if (!empty($dateTo)) {
    $where .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$countRow = $db->fetchOne("SELECT COUNT(*) as total FROM orders o WHERE $where", $params, $types);
$pagination = getPagination($countRow['total'], $page, $perPage);

$orders = $db->fetchAll(
    "SELECT o.*, u.name as user_name, u.phone as user_phone,
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
     FROM orders o
     JOIN users u ON o.user_id = u.id
     WHERE $where
     ORDER BY o.created_at DESC
     LIMIT $perPage OFFSET {$pagination['offset']}",
    $params, $types
);

$statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];
?>

<h1 class="admin-page-title">Orders Management</h1>

<!-- Orders Table -->
<div class="admin-card">
    <div class="admin-card-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <h2>All Orders (<?= $pagination['total'] ?>)</h2>
        <!-- Filters -->
        <form method="GET" class="admin-filters" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin:0; min-width:140px;">
                <select name="status" class="form-input form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0; min-width:140px;">
                <input type="date" name="date_from" class="form-input form-select-sm" value="<?= $dateFrom ?>" style="padding:6px 10px;">
            </div>
            <div class="form-group" style="margin:0; min-width:140px;">
                <input type="date" name="date_to" class="form-input form-select-sm" value="<?= $dateTo ?>" style="padding:6px 10px;">
            </div>
            <div style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="<?= SITE_URL ?>/admin/pages/orders.php" class="btn btn-sm" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;">Reset</a>
            </div>
        </form>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center">No orders found</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?= $order['order_number'] ?></strong></td>
                            <td>
                                <div><?= htmlspecialchars($order['user_name']) ?></div>
                                <small class="text-muted"><?= $order['user_phone'] ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $order['items_count'] ?> item<?= $order['items_count'] > 1 ? 's' : '' ?></span>
                            </td>
                            <td><strong><?= formatPrice($order['total_amount']) ?></strong></td>
                            <td>
                                <div><?= strtoupper($order['payment_method']) ?></div>
                                <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst($order['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <select class="form-input form-select form-select-sm order-status-select" data-id="<?= $order['id'] ?>" data-prev="<?= $order['order_status'] ?>" style="min-width:150px;">
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $order['order_status'] === $s ? 'selected' : '' ?>>
                                            <?= ucwords(str_replace('_', ' ', $s)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <div><?= date('d M Y', strtotime($order['created_at'])) ?></div>
                                <small class="text-muted"><?= date('h:i A', strtotime($order['created_at'])) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="admin-pagination" style="display:flex; justify-content:center; gap:5px; padding:20px;">
            <?php if ($pagination['has_prev']): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?>&status=<?= $status ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" class="btn btn-sm btn-outline-primary">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= $status ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>"
                   class="btn btn-sm <?= $i === $pagination['current_page'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>&status=<?= $status ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" class="btn btn-sm btn-outline-primary">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$adminPageScript = <<<'JS'
$(document).ready(function() {
    // Update order status via AJAX
    $('.order-status-select').on('change', function() {
        const $select = $(this);
        const orderId = $select.data('id');
        const newStatus = $select.val();
        const prevStatus = $select.data('prev') || newStatus;

        // Store previous so we can revert on failure
        $select.data('prev', newStatus);
        $select.prop('disabled', true);

        $.ajax({
            url: SITE_URL + '/api/orders/?action=update-status',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            xhrFields: { withCredentials: true },
            data: JSON.stringify({ order_id: orderId, status: newStatus }),
            success: function(res) {
                $select.prop('disabled', false);
                if (res.success) {
                    showToast('Status updated to "' + newStatus.replace(/_/g, ' ') + '"', 'success');
                } else {
                    $select.val(prevStatus).data('prev', prevStatus);
                    showToast(res.message || 'Failed to update status', 'error');
                }
            },
            error: function(xhr) {
                $select.prop('disabled', false);
                $select.val(prevStatus).data('prev', prevStatus);
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to update order status';
                showToast(msg, 'error');
            }
        });
    });
});
JS;
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
