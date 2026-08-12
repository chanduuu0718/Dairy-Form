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
                            <input type="text" name="login" class="form-input" placeholder="Enter phone number or email" autocomplete="username" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-password-wrapper">
                                <input type="password" name="password" class="form-input" placeholder="Enter password" autocomplete="current-password" required>
                                <button type="button" class="password-toggle" aria-label="Show password" onclick="$(this).prev().attr('type', $(this).prev().attr('type')==='password'?'text':'password'); $(this).find('i').toggleClass('fa-eye fa-eye-slash')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="auth-divider"><span>OR</span></div>

                    <button class="btn btn-outline-primary btn-block" id="show-otp-login" type="button">
                        <i class="fas fa-mobile-alt"></i> Login with OTP
                    </button>
                </div>

                <!-- Login with OTP -->
                <div id="login-otp-form" class="hidden">
                    <form id="otp-form" class="form">
                        <div class="form-group" id="otp-phone-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="otp_phone" class="form-input" inputmode="numeric" autocomplete="tel" placeholder="Enter 10-digit phone number" maxlength="10" required>
                            <button type="button" class="btn btn-primary btn-block mt-2" id="send-otp-btn">
                                <i class="fas fa-paper-plane"></i> Send OTP
                            </button>
                        </div>

                        <div class="form-group hidden" id="otp-input-group">
                            <label class="form-label">Enter the 6-digit OTP</label>
                            <input type="text" name="otp_code" class="form-input otp-input" inputmode="numeric" autocomplete="one-time-code" placeholder="6-digit OTP" maxlength="6" pattern="\d{6}">
                            <small class="form-help">The OTP is valid for 5 minutes.</small>
                            <button type="button" class="btn btn-primary btn-block mt-2" id="verify-otp-btn">
                                <i class="fas fa-check"></i> Verify & Login
                            </button>
                            <button type="button" class="btn btn-link btn-block mt-2" id="resend-otp-btn" disabled>
                                Resend OTP <span id="otp-countdown">(60s)</span>
                            </button>
                        </div>
                    </form>

                    <button class="btn btn-link btn-block mt-2" id="show-password-login" type="button">
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
    let otpCountdownTimer = null;

    function startOtpCountdown(seconds) {
        clearInterval(otpCountdownTimer);
        let remaining = seconds;
        const $resend = $('#resend-otp-btn');
        const $countdown = $('#otp-countdown');

        $resend.prop('disabled', true);
        $countdown.text('(' + remaining + 's)');

        otpCountdownTimer = setInterval(function() {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(otpCountdownTimer);
                $countdown.text('');
                $resend.prop('disabled', false).html('<i class="fas fa-redo"></i> Resend OTP');
                return;
            }
            $countdown.text('(' + remaining + 's)');
        }, 1000);
    }

    function showOtpStep() {
        $('#otp-input-group').removeClass('hidden');
        $('#otp-code').trigger('focus');
        startOtpCountdown(60);
    }

    // Toggle between password and OTP login
    $('#show-otp-login').on('click', function() {
        $('#login-password-form').addClass('hidden');
        $('#login-otp-form').removeClass('hidden');
        $('[name="otp_phone"]').trigger('focus');
    });

    $('#show-password-login').on('click', function() {
        clearInterval(otpCountdownTimer);
        $('#login-otp-form').addClass('hidden');
        $('#login-password-form').removeClass('hidden');
    });

    // Password login
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        const login = $('[name="login"]').val().trim();
        const password = $('[name="password"]').val();
        PMAuth.login(login, password, function(res) {
            if (res.success) {
                const redirect = '<?= htmlspecialchars($_SESSION['redirect_after_login'] ?? '', ENT_QUOTES, 'UTF-8') ?>';
                setTimeout(() => {
                    window.location.href = redirect || SITE_URL + '/';
                }, 700);
            }
        });
    });

    // Send OTP through the configured SMS provider
    $('#send-otp-btn').on('click', function() {
        const phone = $('[name="otp_phone"]').val().replace(/\D/g, '');
        $('[name="otp_phone"]').val(phone);

        if (!/^[6-9]\d{9}$/.test(phone)) {
            showToast('Enter a valid 10-digit Indian mobile number', 'error');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending OTP...');

        $.ajax({
            url: SITE_URL + '/api/auth/send-otp.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ phone: phone }),
            success: function(res) {
                if (res.success) {
                    showToast('OTP sent to your mobile number.', 'success');
                    showOtpStep();
                } else {
                    showToast(res.message || 'Unable to send OTP', 'error');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                showToast(res.message || 'Unable to send OTP. Please try again.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send OTP');
            }
        });
    });

    // Resend OTP
    $('#resend-otp-btn').on('click', function() {
        $('#send-otp-btn').trigger('click');
    });

    // Verify OTP
    $('#verify-otp-btn').on('click', function() {
        const phone = $('[name="otp_phone"]').val().replace(/\D/g, '');
        const otp = $('[name="otp_code"]').val().replace(/\D/g, '');
        $('[name="otp_code"]').val(otp);

        if (!/^[6-9]\d{9}$/.test(phone)) {
            showToast('Enter a valid phone number', 'error');
            return;
        }
        if (!/^\d{6}$/.test(otp)) {
            showToast('Enter the 6-digit OTP', 'error');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying...');

        $.ajax({
            url: SITE_URL + '/api/auth/verify-otp.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ phone: phone, otp: otp }),
            success: function(res) {
                if (res.success) {
                    clearInterval(otpCountdownTimer);
                    showToast('Login successful! Welcome back, ' + res.user.name + '.', 'success');
                    setTimeout(() => location.reload(), 700);
                } else {
                    showToast(res.message || 'OTP verification failed', 'error');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                showToast(res.message || 'OTP verification failed', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Verify & Login');
            }
        });
    });
});
</script>
