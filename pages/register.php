<?php
/**
 * Register Page
 */
require_once __DIR__ . '/../config/config.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/');
    exit;
}

$pageTitle = 'Register';
$bodyClass = 'auth-page';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section auth-section">
    <div class="container">
        <div class="auth-wrapper animate-on-scroll fade-up">
            <div class="auth-card">
                <div class="auth-header">
                    <span class="logo-icon">🐄</span>
                    <h1>Create Account</h1>
                    <p>Join PM Dairy and get farm-fresh products delivered daily</p>
                </div>

                <form id="register-form" class="form">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" class="form-input" placeholder="10-digit mobile number" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="your@email.com (optional)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <div class="input-password-wrapper">
                            <input type="password" name="password" class="form-input" placeholder="Min 6 characters" minlength="6" required>
                            <button type="button" class="password-toggle" onclick="$(this).prev().attr('type', $(this).prev().attr('type')==='password'?'text':'password'); $(this).find('i').toggleClass('fa-eye fa-eye-slash')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" class="form-input form-textarea" rows="2" placeholder="Your delivery address (optional)"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="register-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="<?= SITE_URL ?>/pages/login.php">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#register-btn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');

        PMAuth.register({
            name: $('[name="name"]').val(),
            phone: $('[name="phone"]').val(),
            email: $('[name="email"]').val(),
            password: $('[name="password"]').val(),
            address: $('[name="address"]').val()
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Create Account');
            if (res.success) {
                setTimeout(() => window.location.href = SITE_URL + '/', 1500);
            }
        });
    });
});
</script>
