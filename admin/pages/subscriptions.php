<?php
/**
 * Admin: Subscriptions Management
 */
$adminTitle = 'Subscriptions';
require_once __DIR__ . '/../includes/header.php';

// Fetch all subscriptions with user info
$statusFilter = sanitize($_GET['status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = "1=1";
$params = [];
$types = '';

if (!empty($statusFilter)) {
    $where .= " AND s.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

$countRow = $db->fetchOne("SELECT COUNT(*) as total FROM subscriptions s WHERE $where", $params, $types);
$pagination = getPagination($countRow['total'], $page, $perPage);

$subscriptions = $db->fetchAll(
    "SELECT s.*, u.name as user_name, u.phone as user_phone
     FROM subscriptions s
     JOIN users u ON s.user_id = u.id
     WHERE $where
     ORDER BY s.created_at DESC
     LIMIT $perPage OFFSET {$pagination['offset']}",
    $params, $types
);

// Fetch items for each subscription
foreach ($subscriptions as &$sub) {
    $sub['items'] = $db->fetchAll(
        "SELECT si.*, p.name, p.price, p.unit FROM subscription_items si
         JOIN products p ON si.product_id = p.id
         WHERE si.subscription_id = ?",
        [$sub['id']], 'i'
    );
}
unset($sub);

$subStatuses = ['active', 'paused', 'cancelled', 'expired'];
?>

<h1 class="admin-page-title">Subscriptions Management</h1>

<!-- Filters -->
<div class="admin-card mb-4">
    <form method="GET" style="display:flex; gap:15px; align-items:flex-end; padding:20px; flex-wrap:wrap;">
        <div class="form-group" style="min-width:160px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-input form-select">
                <option value="">All Statuses</option>
                <?php foreach ($subStatuses as $s): ?>
                    <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= SITE_URL ?>/admin/pages/subscriptions.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Subscriptions Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Subscriptions (<?= $pagination['total'] ?>)</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Products</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscriptions)): ?>
                    <tr><td colspan="8" class="text-center">No subscriptions found</td></tr>
                <?php else: ?>
                    <?php foreach ($subscriptions as $sub): ?>
                        <tr id="sub-row-<?= $sub['id'] ?>">
                            <td><strong>#<?= $sub['id'] ?></strong></td>
                            <td>
                                <div><?= htmlspecialchars($sub['user_name']) ?></div>
                                <small class="text-muted"><?= $sub['user_phone'] ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= ucfirst($sub['plan_type']) ?></span>
                                <?php if (!empty($sub['delivery_time'])): ?>
                                    <br><small class="text-muted"><?= ucfirst($sub['delivery_time']) ?> delivery</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($sub['items'])): ?>
                                    <?php foreach ($sub['items'] as $item): ?>
                                        <div style="white-space:nowrap; font-size:13px;">
                                            <?= htmlspecialchars($item['name']) ?>
                                            <span class="text-muted">x<?= $item['quantity'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size:13px;">
                                    <div><strong>Start:</strong> <?= date('d M Y', strtotime($sub['start_date'])) ?></div>
                                    <div><strong>End:</strong> <?= date('d M Y', strtotime($sub['end_date'])) ?></div>
                                </div>
                            </td>
                            <td><strong><?= formatPrice($sub['total_price']) ?></strong></td>
                            <td>
                                <?php
                                    $statusBadges = [
                                        'active' => 'badge-success',
                                        'paused' => 'badge-warning',
                                        'cancelled' => 'badge-danger',
                                        'expired' => 'badge-secondary'
                                    ];
                                    $badgeClass = $statusBadges[$sub['status']] ?? 'badge-info';
                                ?>
                                <span class="badge <?= $badgeClass ?>" id="sub-badge-<?= $sub['id'] ?>">
                                    <?= ucfirst($sub['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px; flex-wrap:wrap;">
                                    <?php if ($sub['status'] === 'active'): ?>
                                        <button class="btn btn-sm btn-outline-warning btn-sub-action" data-id="<?= $sub['id'] ?>" data-status="paused" title="Pause">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-sub-action" data-id="<?= $sub['id'] ?>" data-status="cancelled" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($sub['status'] === 'paused'): ?>
                                        <button class="btn btn-sm btn-outline-success btn-sub-action" data-id="<?= $sub['id'] ?>" data-status="active" title="Resume">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-sub-action" data-id="<?= $sub['id'] ?>" data-status="cancelled" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($sub['status'] === 'cancelled' || $sub['status'] === 'expired'): ?>
                                        <span class="text-muted" style="font-size:12px;">No actions</span>
                                    <?php endif; ?>
                                </div>
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
                <a href="?page=<?= $pagination['current_page'] - 1 ?>&status=<?= $statusFilter ?>" class="btn btn-sm btn-outline-primary">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= $statusFilter ?>"
                   class="btn btn-sm <?= $i === $pagination['current_page'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>&status=<?= $statusFilter ?>" class="btn btn-sm btn-outline-primary">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Subscription status change
    $('.btn-sub-action').on('click', function() {
        const subId = $(this).data('id');
        const newStatus = $(this).data('status');
        const actionText = newStatus === 'cancelled' ? 'cancel' : (newStatus === 'paused' ? 'pause' : 'resume');

        if (!confirm('Are you sure you want to ' + actionText + ' this subscription (#' + subId + ')?')) {
            return;
        }

        $.ajax({
            url: SITE_URL + '/api/subscriptions/?action=update',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                subscription_id: subId,
                status: newStatus
            }),
            success: function(res) {
                if (res.success) {
                    showToast('Subscription ' + newStatus + ' successfully', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(res.message || 'Failed to update subscription', 'error');
                }
            },
            error: function() {
                showToast('Failed to update subscription', 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
