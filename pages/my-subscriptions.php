<?php
/**
 * My Subscriptions Page
 */
require_once __DIR__ . '/../config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user = Auth::getUser();
$pageTitle = 'My Subscriptions';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">My Subscriptions</span>
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
                </div>
                <nav class="account-nav">
                    <a href="<?= SITE_URL ?>/pages/profile.php"><i class="fas fa-user"></i> My Profile</a>
                    <a href="<?= SITE_URL ?>/pages/my-orders.php"><i class="fas fa-box"></i> My Orders</a>
                    <a href="<?= SITE_URL ?>/pages/my-subscriptions.php" class="active"><i class="fas fa-sync"></i> Subscriptions</a>
                    <a href="#" onclick="PMAuth.logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>

            <div class="account-main">
                <div class="account-card">
                    <div class="card-header-flex">
                        <h2><i class="fas fa-sync"></i> My Subscriptions</h2>
                        <a href="<?= SITE_URL ?>/pages/subscriptions.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> New Subscription</a>
                    </div>
                    <div id="subs-container">
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
    function loadSubscriptions() {
        $.get(SITE_URL + '/api/subscriptions/', function(res) {
            if (!res.success || !res.subscriptions.length) {
                $('#subs-container').html(`
                    <div class="empty-state">
                        <i class="fas fa-sync"></i>
                        <h3>No subscriptions yet</h3>
                        <p>Start a subscription for hassle-free daily delivery!</p>
                        <a href="${SITE_URL}/pages/subscriptions.php" class="btn btn-primary">View Plans</a>
                    </div>
                `);
                return;
            }

            let html = '';
            res.subscriptions.forEach(function(sub) {
                const statusColors = { active: 'badge-success', paused: 'badge-warning', cancelled: 'badge-danger', expired: 'badge-secondary' };
                let products = sub.items.map(i => `${i.name} x ${i.quantity}`).join(', ');

                html += `
                    <div class="subscription-card">
                        <div class="sub-header">
                            <div>
                                <h3>${sub.plan_type.charAt(0).toUpperCase() + sub.plan_type.slice(1)} Plan</h3>
                                <span class="badge ${statusColors[sub.status]}">${sub.status}</span>
                            </div>
                            <span class="sub-price">₹${parseFloat(sub.total_price).toFixed(2)}</span>
                        </div>
                        <div class="sub-details">
                            <p><i class="fas fa-box"></i> <strong>Products:</strong> ${products}</p>
                            <p><i class="fas fa-calendar"></i> <strong>Period:</strong> ${sub.start_date} to ${sub.end_date}</p>
                            <p><i class="fas fa-clock"></i> <strong>Delivery:</strong> ${sub.delivery_time}</p>
                        </div>
                        <div class="sub-actions">
                            ${sub.status === 'active' ? `
                                <button class="btn btn-sm btn-outline-warning sub-action" data-id="${sub.id}" data-status="paused">Pause</button>
                                <button class="btn btn-sm btn-outline-danger sub-action" data-id="${sub.id}" data-status="cancelled">Cancel</button>
                            ` : ''}
                            ${sub.status === 'paused' ? `
                                <button class="btn btn-sm btn-outline-success sub-action" data-id="${sub.id}" data-status="active">Resume</button>
                                <button class="btn btn-sm btn-outline-danger sub-action" data-id="${sub.id}" data-status="cancelled">Cancel</button>
                            ` : ''}
                        </div>
                    </div>
                `;
            });

            $('#subs-container').html(html);
        });
    }

    loadSubscriptions();

    $(document).on('click', '.sub-action', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');

        $.ajax({
            url: SITE_URL + '/api/subscriptions/?action=update',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ subscription_id: id, status: status }),
            success: function(res) {
                showToast(res.message, res.success ? 'success' : 'error');
                if (res.success) loadSubscriptions();
            }
        });
    });
});
</script>
