<?php
/**
 * Payment Return Page
 * Cashfree redirects here after payment completion
 */
require_once __DIR__ . '/../config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$orderId = intval($_GET['order_id'] ?? 0);
$cfOrderId = sanitize($_GET['cf_order_id'] ?? '');

if (!$orderId || !$cfOrderId) {
    header('Location: ' . SITE_URL . '/pages/my-orders.php');
    exit;
}

$pageTitle = 'Verifying Payment';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="order-confirmation payment-return-container animate-on-scroll fade-up">
            <div class="confirmation-icon" id="status-icon">
                <div class="processing-spinner"></div>
            </div>
            <h1 id="status-title">Verifying Payment...</h1>
            <p class="confirmation-msg" id="status-msg">Please wait while we confirm your payment. Do not close this page.</p>
            <div class="payment-verify-progress" id="verify-progress">
                <div class="payment-verify-bar"></div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    var orderId = <?= $orderId ?>;
    var cfOrderId = '<?= addslashes($cfOrderId) ?>';
    var retries = 0;
    var maxRetries = 10;

    function verifyPayment() {
        $.ajax({
            url: SITE_URL + '/api/payment/?action=verify',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ order_id: orderId, cf_order_id: cfOrderId }),
            success: function(res) {
                if (res.success && res.payment_status === 'paid') {
                    $('#verify-progress').hide();
                    $('#status-icon').html('<div class="processing-success-icon"><i class="fas fa-check-circle"></i></div>');
                    $('#status-title').text('Payment Successful!');
                    $('#status-msg').text('Redirecting to order confirmation...');
                    setTimeout(function() {
                        window.location.href = SITE_URL + '/pages/order-confirmation.php?id=' + orderId;
                    }, 1500);
                } else if (res.payment_status === 'pending' && retries < maxRetries) {
                    retries++;
                    setTimeout(verifyPayment, 3000);
                } else {
                    $('#verify-progress').hide();
                    $('#status-icon').html('<div class="payment-return-failed"><i class="fas fa-times-circle" style="font-size:3rem;"></i></div>');
                    $('#status-title').text('Payment Failed');
                    $('#status-msg').html(
                        (res.message || 'Payment could not be verified.') +
                        '<br><br><a href="' + SITE_URL + '/pages/my-orders.php" class="btn btn-primary">View My Orders</a>' +
                        ' <a href="' + SITE_URL + '/pages/products.php" class="btn btn-outline-primary">Continue Shopping</a>'
                    );
                }
            },
            error: function() {
                if (retries < maxRetries) {
                    retries++;
                    setTimeout(verifyPayment, 3000);
                } else {
                    $('#verify-progress').hide();
                    $('#status-icon').html('<div class="payment-return-warning"><i class="fas fa-exclamation-triangle" style="font-size:3rem;"></i></div>');
                    $('#status-title').text('Verification Error');
                    $('#status-msg').html(
                        'Could not verify payment. Please check your orders.<br><br>' +
                        '<a href="' + SITE_URL + '/pages/my-orders.php" class="btn btn-primary">View My Orders</a>'
                    );
                }
            }
        });
    }

    verifyPayment();
});
</script>
