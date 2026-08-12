<?php
/**
 * Admin: Contact Messages
 */
$adminTitle = 'Messages';
require_once __DIR__ . '/../includes/header.php';

// Fetch all messages
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$countRow = $db->fetchOne("SELECT COUNT(*) as total FROM contacts");
$pagination = getPagination($countRow['total'], $page, $perPage);

$messages = $db->fetchAll(
    "SELECT * FROM contacts ORDER BY created_at DESC LIMIT $perPage OFFSET {$pagination['offset']}"
);

$unreadCount = $db->fetchOne("SELECT COUNT(*) as c FROM contacts WHERE is_read = 0");
?>

<h1 class="admin-page-title">Contact Messages</h1>

<!-- Stats -->
<div class="admin-card mb-4">
    <div style="display:flex; gap:30px; padding:20px; flex-wrap:wrap;">
        <div>
            <strong style="font-size:24px;"><?= $pagination['total'] ?></strong>
            <div class="text-muted">Total Messages</div>
        </div>
        <div>
            <strong style="font-size:24px; color:#e74c3c;"><?= $unreadCount['c'] ?></strong>
            <div class="text-muted">Unread</div>
        </div>
    </div>
</div>

<!-- Messages Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Messages</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="7" class="text-center">No messages found</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr id="msg-row-<?= $msg['id'] ?>" class="msg-row <?= $msg['is_read'] ? '' : 'unread-row' ?>" data-id="<?= $msg['id'] ?>" style="cursor:pointer; <?= $msg['is_read'] ? '' : 'background:#fffde7;' ?>">
                            <td>
                                <strong><?= htmlspecialchars($msg['name']) ?></strong>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="badge badge-danger" style="font-size:10px; margin-left:5px;">NEW</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($msg['email']) ?></td>
                            <td><?= htmlspecialchars($msg['phone'] ?? '-') ?></td>
                            <td><strong><?= htmlspecialchars($msg['subject']) ?></strong></td>
                            <td>
                                <span class="msg-preview"><?= htmlspecialchars(truncateText($msg['message'], 60)) ?></span>
                            </td>
                            <td>
                                <small><?= date('d M Y', strtotime($msg['created_at'])) ?></small><br>
                                <small class="text-muted"><?= date('h:i A', strtotime($msg['created_at'])) ?></small>
                            </td>
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Read</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Unread</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Expanded message row (hidden by default) -->
                        <tr class="msg-detail-row" id="msg-detail-<?= $msg['id'] ?>" style="display:none;">
                            <td colspan="7" style="background:#f9f9f9; padding:20px;">
                                <div style="margin-bottom:10px;">
                                    <strong>From:</strong> <?= htmlspecialchars($msg['name']) ?>
                                    &lt;<?= htmlspecialchars($msg['email']) ?>&gt;
                                    <?php if (!empty($msg['phone'])): ?>
                                        | <strong>Phone:</strong> <?= htmlspecialchars($msg['phone']) ?>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-bottom:10px;">
                                    <strong>Subject:</strong> <?= htmlspecialchars($msg['subject']) ?>
                                </div>
                                <div style="margin-bottom:15px; white-space:pre-wrap; line-height:1.6; background:#fff; padding:15px; border-radius:8px; border:1px solid #eee;">
<?= htmlspecialchars($msg['message']) ?>
                                </div>
                                <div style="display:flex; gap:10px;">
                                    <?php if (!$msg['is_read']): ?>
                                        <button class="btn btn-sm btn-primary btn-mark-read" data-id="<?= $msg['id'] ?>">
                                            <i class="fas fa-check"></i> Mark as Read
                                        </button>
                                    <?php endif; ?>
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= htmlspecialchars($msg['subject']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-reply"></i> Reply via Email
                                    </a>
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
                <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="btn btn-sm btn-outline-primary">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <a href="?page=<?= $i ?>"
                   class="btn btn-sm <?= $i === $pagination['current_page'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="btn btn-sm btn-outline-primary">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Toggle message detail on row click
    $('.msg-row').on('click', function() {
        const msgId = $(this).data('id');
        const $detail = $('#msg-detail-' + msgId);

        // Close other open details
        $('.msg-detail-row').not($detail).slideUp(200);

        // Toggle this detail
        $detail.slideToggle(200);

        // Auto-mark as read when expanded
        const $row = $(this);
        if ($row.hasClass('unread-row')) {
            markAsRead(msgId, $row);
        }
    });

    // Mark as read button
    $(document).on('click', '.btn-mark-read', function(e) {
        e.stopPropagation();
        const msgId = $(this).data('id');
        const $row = $('#msg-row-' + msgId);
        markAsRead(msgId, $row);
        $(this).fadeOut();
    });

    function markAsRead(msgId, $row) {
        $.ajax({
            url: SITE_URL + '/api/contact/?action=mark-read',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: msgId }),
            success: function(res) {
                if (res.success) {
                    $row.removeClass('unread-row').css('background', '');
                    $row.find('.badge-danger').remove();
                    $row.find('.badge-warning').replaceWith('<span class="badge badge-success"><i class="fas fa-check"></i> Read</span>');
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
