<?php
/**
 * My Profile Page
 */
require_once __DIR__ . '/../config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$user = Auth::getUser();
$db = Database::getInstance();
$addresses = $db->fetchAll("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC", [$user['id']], 'i');

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">My Profile</span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="account-layout">
            <!-- Sidebar -->
            <aside class="account-sidebar">
                <div class="account-user-card">
                    <div class="account-avatar">
                        <div class="avatar-placeholder avatar-lg"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    </div>
                    <h3><?= htmlspecialchars($user['name']) ?></h3>
                    <p><?= htmlspecialchars($user['phone']) ?></p>
                </div>
                <nav class="account-nav">
                    <a href="<?= SITE_URL ?>/pages/profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
                    <a href="<?= SITE_URL ?>/pages/my-orders.php"><i class="fas fa-box"></i> My Orders</a>
                    <a href="<?= SITE_URL ?>/pages/my-subscriptions.php"><i class="fas fa-sync"></i> Subscriptions</a>
                    <a href="#" id="logout-sidebar"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Profile Info -->
                <div class="account-card">
                    <h2><i class="fas fa-user-edit"></i> Personal Information</h2>
                    <form id="profile-form" class="form">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-input" value="<?= htmlspecialchars($user['phone']) ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="your@email.com">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </div>

                <!-- Addresses -->
                <div class="account-card">
                    <h2><i class="fas fa-map-marker-alt"></i> Saved Addresses</h2>
                    <div class="addresses-grid" id="addresses-list">
                        <?php foreach ($addresses as $addr): ?>
                            <div class="address-card" data-id="<?= $addr['id'] ?>">
                                <div class="address-label"><?= htmlspecialchars($addr['label']) ?> <?= $addr['is_default'] ? '<span class="badge badge-success">Default</span>' : '' ?></div>
                                <p><?= htmlspecialchars($addr['address_line1']) ?></p>
                                <?php if ($addr['address_line2']): ?>
                                    <p><?= htmlspecialchars($addr['address_line2']) ?></p>
                                <?php endif; ?>
                                <p><?= htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']) ?></p>
                                <button class="btn btn-sm btn-outline-danger delete-address" data-id="<?= $addr['id'] ?>">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="btn btn-outline-primary mt-3" id="show-add-address"><i class="fas fa-plus"></i> Add Address</button>

                    <div id="add-address-form" class="hidden mt-3">
                        <form id="address-form" class="form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Label</label>
                                    <select name="label" class="form-input form-select">
                                        <option>Home</option><option>Office</option><option>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pincode *</label>
                                    <input type="text" name="pincode" class="form-input" maxlength="6" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address Line 1 *</label>
                                <input type="text" name="address_line1" class="form-input" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">City *</label>
                                    <input type="text" name="city" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-input" value="Haryana">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Address</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="account-card">
                    <h2><i class="fas fa-lock"></i> Change Password</h2>
                    <form id="password-form" class="form">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-input" minlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    // Update profile
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + '/api/users/?action=update-profile',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ name: $('[name="name"]').val(), email: $('[name="email"]').val() }),
            success: function(res) { showToast(res.message, res.success ? 'success' : 'error'); },
            error: function(xhr) { showToast(xhr.responseJSON?.message || 'Update failed', 'error'); }
        });
    });

    // Add address
    $('#show-add-address').on('click', function() { $('#add-address-form').toggleClass('hidden'); });

    $('#address-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + '/api/users/?action=add-address',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                label: $('[name="label"]').val(),
                address_line1: $('[name="address_line1"]').val(),
                city: $('[name="city"]').val(),
                state: $('[name="state"]').val(),
                pincode: $('[name="pincode"]').val(),
                is_default: 0
            }),
            success: function(res) {
                if (res.success) { showToast(res.message, 'success'); setTimeout(() => location.reload(), 1000); }
            }
        });
    });

    // Delete address
    $(document).on('click', '.delete-address', function() {
        const id = $(this).data('id');
        $.ajax({
            url: SITE_URL + '/api/users/?action=delete-address',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ address_id: id }),
            success: function(res) {
                if (res.success) { $(`.address-card[data-id="${id}"]`).fadeOut(); showToast('Address removed', 'info'); }
            }
        });
    });

    // Change password
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: SITE_URL + '/api/users/?action=change-password',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                current_password: $('[name="current_password"]').val(),
                new_password: $('[name="new_password"]').val()
            }),
            success: function(res) { showToast(res.message, res.success ? 'success' : 'error'); if (res.success) $('#password-form')[0].reset(); },
            error: function(xhr) { showToast(xhr.responseJSON?.message || 'Failed', 'error'); }
        });
    });

    $('#logout-sidebar').on('click', function(e) { e.preventDefault(); PMAuth.logout(); });
});
</script>
