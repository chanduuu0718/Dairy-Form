<?php
/**
 * Admin: Farm Visits Management
 */
$adminTitle = 'Farm Visits';
require_once __DIR__ . '/../includes/header.php';

// Filters
$status = sanitize($_GET['status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = "1=1";
$params = [];
$types = '';

if (!empty($status)) {
    $where .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}

$countRow = $db->fetchOne("SELECT COUNT(*) as total FROM farm_visits WHERE $where", $params, $types);
$pagination = getPagination($countRow['total'], $page, $perPage);

$visits = $db->fetchAll(
    "SELECT * FROM farm_visits WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET {$pagination['offset']}",
    $params, $types
);

$visitStatuses = ['pending', 'confirmed', 'rejected', 'completed'];
?>

<h1 class="admin-page-title">Farm Visit Bookings</h1>

<!-- Filters -->
<div class="admin-card mb-4">
    <form method="GET" style="display:flex; gap:15px; align-items:flex-end; padding:20px; flex-wrap:wrap;">
        <div class="form-group" style="min-width:160px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-input form-select">
                <option value="">All Statuses</option>
                <?php foreach ($visitStatuses as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= SITE_URL ?>/admin/pages/farm-visits.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Visits Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Bookings (<?= $pagination['total'] ?>)</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Preferred Date</th>
                    <th>Visitors</th>
                    <th>Status</th>
                    <th>Admin Notes</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visits)): ?>
                    <tr><td colspan="8" class="text-center">No farm visit bookings found</td></tr>
                <?php else: ?>
                    <?php foreach ($visits as $visit): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($visit['name']) ?></strong>
                                <?php if (!empty($visit['message'])): ?>
                                    <br><small class="text-muted" title="<?= htmlspecialchars($visit['message']) ?>">
                                        <i class="fas fa-comment"></i> <?= htmlspecialchars(truncateText($visit['message'], 40)) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($visit['phone']) ?></td>
                            <td><?= htmlspecialchars($visit['email'] ?? '-') ?></td>
                            <td>
                                <strong><?= date('d M Y', strtotime($visit['preferred_date'])) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $visit['visitors'] ?></span>
                            </td>
                            <td>
                                <select class="form-input form-select form-select-sm visit-status-select" data-id="<?= $visit['id'] ?>" style="min-width:120px;">
                                    <?php foreach ($visitStatuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $visit['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-input form-input-sm visit-admin-notes"
                                       data-id="<?= $visit['id'] ?>"
                                       value="<?= htmlspecialchars($visit['admin_notes'] ?? '') ?>"
                                       placeholder="Add notes..."
                                       style="min-width:150px; font-size:13px;">
                            </td>
                            <td>
                                <small><?= date('d M Y, h:i A', strtotime($visit['created_at'])) ?></small>
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
                <a href="?page=<?= $pagination['current_page'] - 1 ?>&status=<?= $status ?>" class="btn btn-sm btn-outline-primary">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= $status ?>"
                   class="btn btn-sm <?= $i === $pagination['current_page'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>&status=<?= $status ?>" class="btn btn-sm btn-outline-primary">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Update visit status
    $('.visit-status-select').on('change', function() {
        const visitId = $(this).data('id');
        const newStatus = $(this).val();
        const notes = $(`.visit-admin-notes[data-id="${visitId}"]`).val();

        $.ajax({
            url: SITE_URL + '/api/farm-visits/?action=update-status',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                visit_id: visitId,
                status: newStatus,
                admin_notes: notes
            }),
            success: function(res) {
                if (res.success) {
                    showToast('Visit status updated to ' + newStatus, 'success');
                } else {
                    showToast(res.message || 'Failed to update status', 'error');
                }
            },
            error: function() {
                showToast('Failed to update visit status', 'error');
            }
        });
    });

    // Save admin notes on blur
    let notesTimeout;
    $('.visit-admin-notes').on('input', function() {
        const $input = $(this);
        const visitId = $input.data('id');
        const notes = $input.val();
        const status = $(`.visit-status-select[data-id="${visitId}"]`).val();

        clearTimeout(notesTimeout);
        notesTimeout = setTimeout(function() {
            $.ajax({
                url: SITE_URL + '/api/farm-visits/?action=update-status',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    visit_id: visitId,
                    status: status,
                    admin_notes: notes
                }),
                success: function(res) {
                    if (res.success) {
                        showToast('Notes saved', 'success');
                    }
                }
            });
        }, 800);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
