<?php
/**
 * Login Page
 */
require_once __DIR__ . '/../config/config.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/');
    exit;
}

$pageTitle = 'Login';
$bodyClass = 'auth-page';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section auth-section">
    <div class="container">
        <div class="auth-wrapper animate-on-scroll fade-up">
            <div class="auth-card">
                <div class="auth-header">
                    <span class="logo-icon">🐄</span>
                    <h1>Welcome Back</h1>
                    <p>Login to your PM Dairy account</p>
                </div>

                <!-- Login with Password -->
                <div id="login-password-form">
                    <form id="login-form" class="form">
                        <div class="form-group">
                            <label class="form-label">Phone or Email</label>
                            <input type="text" name="login" class="form-input" placeholder="Enter phone number or email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-password-wrapper">
                                <input type="password" name="password" class="form-input" placeholder="Enter password" required>
                                <button type="button" class="password-toggle" onclick="$(this).prev().attr('type', $(this).prev().attr('type')==='password'?'text':'password'); $(this).find('i').toggleClass('fa-eye fa-eye-slash')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="auth-divider"><span>OR</span></div>

                    <button class="btn btn-outline-primary btn-block" id="show-otp-login">
                        <i class="fas fa-mobile-alt"></i> Login with OTP
                    </button>
                </div>

                <!-- Login with OTP -->
                <div id="login-otp-form" class="hidden">
                    <form id="otp-form" class="form">
                        <div class="form-group" id="otp-phone-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="otp_phone" class="form-input" placeholder="Enter 10-digit phone number" required>
                            <button type="button" class="btn btn-primary btn-block mt-2" id="send-otp-btn">
                                <i class="fas fa-paper-plane"></i> Send OTP
                            </button>
                        </div>
                        <div class="form-group hidden" id="otp-input-group">
                            <label class="form-label">Enter OTP</label>
                            <input type="text" name="otp_code" class="form-input otp-input" placeholder="6-digit OTP" maxlength="6">
                            <button type="button" class="btn btn-primary btn-block mt-2" id="verify-otp-btn">
                                <i class="fas fa-check"></i> Verify & Login
                            </button>
                        </div>
                    </form>

                    <button class="btn btn-link btn-block mt-2" id="show-password-login">
                        <i class="fas fa-arrow-left"></i> Back to password login
                    </button>
                </div>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="<?= SITE_URL ?>/pages/register.php">Register Now</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    // Toggle between password and OTP login
    $('#show-otp-login').on('click', function() {
        $('#login-password-form').addClass('hidden');
        $('#login-otp-form').removeClass('hidden');
    });

    $('#show-password-login').on('click', function() {
        $('#login-otp-form').addClass('hidden');
        $('#login-password-form').removeClass('hidden');
    });

    // Password login
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        const login = $('[name="login"]').val();
        const password = $('[name="password"]').val();
        PMAuth.login(login, password, function(res) {
            if (res.success) {
                const redirect = '<?= $_SESSION['redirect_after_login'] ?? '' ?>';
                setTimeout(() => {
                    window.location.href = redirect || SITE_URL + '/';
                }, 1000);
            }
        });
    });

    // Send OTP
    $('#send-otp-btn').on('click', function() {
        const phone = $('[name="otp_phone"]').val();
        if (phone.length !== 10) {
            showToast('Enter valid 10-digit phone number', 'error');
            return;
        }
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: SITE_URL + '/api/auth/send-otp.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ phone: phone }),
            success: function(res) {
                if (res.success) {
                    showToast('OTP sent to your phone!', 'success');
                    $('#otp-input-group').removeClass('hidden');
                    if (res.otp_debug) showToast('Dev OTP: ' + res.otp_debug, 'info', 10000);
                } else {
                    showToast(res.message, 'error');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Resend OTP');
            }
        });
    });

    // Verify OTP
    $('#verify-otp-btn').on('click', function() {
        const phone = $('[name="otp_phone"]').val();
        const otp = $('[name="otp_code"]').val();

        $.ajax({
            url: SITE_URL + '/api/auth/verify-otp.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ phone: phone, otp: otp }),
            success: function(res) {
                if (res.success) {
                    showToast('Login successful!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.message, 'error');
                }
            }
        });
    });
});
</script>
