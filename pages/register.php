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
$next = trim($_GET['next'] ?? '');
if ($next !== '' && (str_starts_with($next, '/') || str_starts_with($next, SITE_URL . '/'))) {
    $_SESSION['redirect_after_login'] = $next;
}
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section auth-section">
    <div class="container">
        <div class="auth-wrapper animate-on-scroll fade-up">
            <div class="auth-card">
                <div class="auth-header">
                    <span class="logo-icon">🐄</span>
                    <h1>Create Account</h1>
                    <p id="register-subtitle">Join PM Dairy and get farm-fresh products delivered daily</p>
                </div>

                <form id="register-form" class="form">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-input" autocomplete="name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" class="form-input" inputmode="numeric" autocomplete="tel" placeholder="10-digit mobile number" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" autocomplete="email" placeholder="your@email.com (optional)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <div class="input-password-wrapper">
                            <input type="password" name="password" class="form-input" autocomplete="new-password" placeholder="Min 6 characters" minlength="6" required>
                            <button type="button" class="password-toggle" aria-label="Show password" onclick="$(this).prev().attr('type', $(this).prev().attr('type')==='password'?'text':'password'); $(this).find('i').toggleClass('fa-eye fa-eye-slash')"><i class="fas fa-eye"></i></button>
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

                <div id="register-otp-step" class="hidden">
                    <div class="form-group">
                        <label class="form-label">Verify your mobile number</label>
                        <p class="form-help">Enter the 6-digit OTP sent to <strong id="register-phone-display"></strong>.</p>
                        <input type="text" id="register-otp" class="form-input otp-input" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="\d{6}" placeholder="6-digit OTP">
                    </div>
                    <button type="button" class="btn btn-primary btn-lg btn-block" id="verify-register-otp">
                        <i class="fas fa-check"></i> Verify & Continue
                    </button>
                    <button type="button" class="btn btn-link btn-block mt-2" id="resend-register-otp" disabled>
                        Resend OTP <span id="register-otp-countdown">(60s)</span>
                    </button>
                </div>

                <div class="auth-footer">
                    <p>Already have an account? <a href="<?= SITE_URL ?>/pages/login.php<?= $next !== '' ? '?next=' . urlencode($next) : '' ?>">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    let pendingPhone = '';
    let otpTimer = null;
    const redirectAfterLogin = <?= json_encode($_SESSION['redirect_after_login'] ?? '') ?>;

    function startCountdown(seconds) {
        clearInterval(otpTimer);
        let remaining = seconds;
        $('#resend-register-otp').prop('disabled', true);
        $('#register-otp-countdown').text('(' + remaining + 's)');
        otpTimer = setInterval(function() {
            remaining--;
            if (remaining <= 0) {
                clearInterval(otpTimer);
                $('#register-otp-countdown').text('');
                $('#resend-register-otp').prop('disabled', false).html('<i class="fas fa-redo"></i> Resend OTP');
                return;
            }
            $('#register-otp-countdown').text('(' + remaining + 's)');
        }, 1000);
    }

    function sendRegistrationOtp() {
        const $btn = $('#resend-register-otp');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        $.ajax({
            url: SITE_URL + '/api/auth/send-otp.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ phone: pendingPhone }),
            success: function(res) {
                if (res.success) {
                    showToast('Verification OTP sent.', 'success');
                    startCountdown(60);
                } else {
                    showToast(res.message || 'Unable to send OTP', 'error');
                    $btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                showToast(res.message || 'Unable to send OTP', 'error');
                $btn.prop('disabled', false);
            }
        });
    }

    // Signup deliberately does not log in until OTP verification succeeds.
    PMAuth.register = function(data, callback) {
        $.ajax({
            url: SITE_URL + '/api/auth/register.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                if (res.success && res.otp_required) {
                    pendingPhone = data.phone.replace(/\D/g, '');
                    $('#register-form').addClass('hidden');
                    $('#register-otp-step').removeClass('hidden');
                    $('#register-phone-display').text('+91 ' + pendingPhone);
                    $('#register-subtitle').text('Verify your mobile number to activate your account.');
                    $('#register-otp').trigger('focus');
                    startCountdown(60);
                    showToast('Account created. Enter the OTP sent to your phone.', 'success');
                } else if (!res.success) {
                    showToast(res.message || 'Registration failed', 'error');
                }
                if (callback) callback(res);
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                if (res.errors) res.errors.forEach(err => showToast(err, 'error'));
                else showToast(res.message || 'Registration failed', 'error');
                if (callback) callback(res);
            }
        });
    };

    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        const phone = $('[name="phone"]').val().replace(/\D/g, '');
        $('[name="phone"]').val(phone);
        if (!/^[6-9]\d{9}$/.test(phone)) {
            showToast('Enter a valid 10-digit Indian mobile number', 'error');
            return;
        }
        const $btn = $('#register-btn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');
        PMAuth.register({
            name: $('[name="name"]').val().trim(),
            phone: phone,
            email: $('[name="email"]').val().trim(),
            password: $('[name="password"]').val(),
            address: $('[name="address"]').val().trim()
        }, function() {
            $btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Create Account');
        });
    });

    $('#verify-register-otp').on('click', function() {
        const otp = $('#register-otp').val().replace(/\D/g, '');
        $('#register-otp').val(otp);
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
            data: JSON.stringify({ phone: pendingPhone, otp: otp }),
            success: function(res) {
                if (res.success) {
                    clearInterval(otpTimer);
                    showToast('Mobile verified. Welcome to PM Dairy!', 'success');
                    setTimeout(function() {
                        window.location.href = redirectAfterLogin || SITE_URL + '/';
                    }, 700);
                } else {
                    showToast(res.message || 'OTP verification failed', 'error');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                showToast(res.message || 'OTP verification failed', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Verify & Continue');
            }
        });
    });

    $('#resend-register-otp').on('click', sendRegistrationOtp);
});
</script>
