<?php
/**
 * Checkout Page
 */
require_once __DIR__ . '/../config/config.php';

// Require login for checkout
if (!Auth::isLoggedIn()) {
    $_SESSION['redirect_after_login'] = SITE_URL . '/pages/checkout.php';
    header('Location: ' . SITE_URL . '/pages/login.php?msg=login_required');
    exit;
}

$user = Auth::getUser();
$db = Database::getInstance();
$addresses = $db->fetchAll("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC", [$user['id']], 'i');
$timeSlots = json_decode(getSetting('delivery_time_slots') ?: '[]', true);

$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a><span>/</span>
            <a href="<?= SITE_URL ?>/pages/cart.php">Cart</a><span>/</span>
            <span class="current">Checkout</span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <h1 class="page-title">Checkout</h1>

        <div class="checkout-layout" id="checkout-container">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <!-- Delivery Address -->
                <div class="checkout-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>

                    <?php if (!empty($addresses)): ?>
                        <div class="saved-addresses">
                            <?php foreach ($addresses as $addr): ?>
                                <label class="address-option">
                                    <input type="radio" name="selected_address" value="<?= $addr['id'] ?>"
                                           data-address="<?= htmlspecialchars($addr['address_line1'] . ', ' . ($addr['address_line2'] ? $addr['address_line2'] . ', ' : '') . $addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']) ?>"
                                           <?= $addr['is_default'] ? 'checked' : '' ?>>
                                    <div class="address-card-mini">
                                        <strong><?= htmlspecialchars($addr['label']) ?></strong>
                                        <p><?= htmlspecialchars($addr['address_line1']) ?></p>
                                        <?php if ($addr['address_line2']): ?>
                                            <p><?= htmlspecialchars($addr['address_line2']) ?></p>
                                        <?php endif; ?>
                                        <p><?= htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']) ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" id="toggle-new-address">
                            <i class="fas fa-plus"></i> Add New Address
                        </button>
                    <?php endif; ?>

                    <div id="new-address-form" class="<?= !empty($addresses) ? 'hidden' : '' ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Address Line 1 *</label>
                                <input type="text" id="addr_line1" class="form-input" placeholder="House/Flat number, Street" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" id="addr_line2" class="form-input" placeholder="Landmark, Colony">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City *</label>
                                <input type="text" id="addr_city" class="form-input" placeholder="City" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pincode *</label>
                                <input type="text" id="addr_pincode" class="form-input" placeholder="6-digit pincode" maxlength="6" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Time -->
                <div class="checkout-card">
                    <h3><i class="fas fa-clock"></i> Delivery Time</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" id="delivery_date" class="form-input" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time Slot</label>
                            <select id="delivery_time" class="form-input form-select">
                                <?php if (!empty($timeSlots)): ?>
                                    <?php foreach ($timeSlots as $slot): ?>
                                        <option value="<?= $slot ?>"><?= $slot ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="6:00 AM - 8:00 AM">6:00 AM - 8:00 AM</option>
                                    <option value="8:00 AM - 10:00 AM">8:00 AM - 10:00 AM</option>
                                    <option value="4:00 PM - 6:00 PM">4:00 PM - 6:00 PM</option>
                                    <option value="6:00 PM - 8:00 PM">6:00 PM - 8:00 PM</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-card">
                    <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="payment-option-content">
                                <i class="fas fa-money-bill-wave"></i>
                                <div>
                                    <strong>Cash on Delivery (COD)</strong>
                                    <p>Pay when your order is delivered</p>
                                </div>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="online">
                            <div class="payment-option-content">
                                <i class="fas fa-mobile-alt"></i>
                                <div>
                                    <strong>Online Payment (Cashfree)</strong>
                                    <p>UPI, Cards, Net Banking, Wallets</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="checkout-card">
                    <h3><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h3>
                    <textarea id="order_notes" class="form-input form-textarea" rows="2" placeholder="Any special instructions for delivery..."></textarea>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="checkout-summary" id="order-summary">
                <h3>Order Summary</h3>
                <div class="summary-loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Order Processing Overlay -->
<div class="order-processing-overlay" id="order-processing-overlay">
    <div class="processing-card">
        <div class="processing-animation" id="processing-animation">
            <div class="processing-spinner"></div>
        </div>
        <h2 class="processing-title" id="processing-title">Processing your order...</h2>
        <p class="processing-msg" id="processing-msg">Please wait while we confirm your order details</p>
        <div class="processing-steps" id="processing-steps">
            <div class="processing-step-item active" id="proc-step-1">
                <i class="fas fa-check-circle"></i> Verifying cart items
            </div>
            <div class="processing-step-item" id="proc-step-2">
                <i class="fas fa-circle"></i> Creating your order
            </div>
            <div class="processing-step-item" id="proc-step-3">
                <i class="fas fa-circle"></i> Confirming details
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
$(document).ready(function() {

    // Initialize Cashfree SDK
    const cashfree = Cashfree({ mode: '<?= CASHFREE_MODE === "TEST" ? "sandbox" : "production" ?>' });

    // ── Toggle new address form ──────────────────────
    $('#toggle-new-address').on('click', function() {
        $('#new-address-form').toggleClass('hidden');
        $('input[name="selected_address"]').prop('checked', false);
    });

    // ── Load order summary ───────────────────────────
    $.get(SITE_URL + '/api/cart/', function(res) {
        if (!res.success || !res.items.length) {
            window.location.href = SITE_URL + '/pages/cart.php';
            return;
        }

        let itemsHtml = '';
        res.items.forEach(function(item) {
            itemsHtml += `
                <div class="summary-item">
                    <span>${item.name} x ${item.quantity}</span>
                    <span>₹${(item.price * item.quantity).toFixed(2)}</span>
                </div>
            `;
        });

        const delivery = res.delivery_charge > 0 ? `₹${res.delivery_charge.toFixed(2)}` : 'FREE';

        $('#order-summary').html(`
            <h3>Order Summary</h3>
            <div class="summary-items">${itemsHtml}</div>
            <hr>
            <div class="summary-row"><span>Subtotal</span><span>₹${res.subtotal.toFixed(2)}</span></div>
            <div class="summary-row"><span>Delivery</span><span>${delivery}</span></div>
            <div class="summary-row summary-total"><span>Total</span><span>₹${res.total.toFixed(2)}</span></div>
            <button class="btn btn-gold btn-lg btn-block mt-3" id="place-order-btn">
                <i class="fas fa-check-circle"></i> Place Order
            </button>
        `);
    });

    // ── Processing Overlay Helpers ───────────────────
    function showProcessingOverlay() {
        // Reset overlay state
        $('#processing-animation').html('<div class="processing-spinner"></div>');
        $('#processing-title').text('Processing your order...');
        $('#processing-msg').text('Please wait while we confirm your order details');
        $('#processing-steps').show();
        $('#proc-step-1').removeClass('done').addClass('active').find('i').attr('class', 'fas fa-spinner fa-spin');
        $('#proc-step-2').removeClass('active done').find('i').attr('class', 'fas fa-circle');
        $('#proc-step-3').removeClass('active done').find('i').attr('class', 'fas fa-circle');

        // Show overlay
        var $overlay = $('#order-processing-overlay');
        $overlay.css('display', 'flex');
        setTimeout(function() { $overlay.addClass('show'); }, 10);

        // Animate steps sequentially
        setTimeout(function() {
            $('#proc-step-1').removeClass('active').addClass('done').find('i').attr('class', 'fas fa-check-circle');
            $('#proc-step-2').addClass('active').find('i').attr('class', 'fas fa-spinner fa-spin');
        }, 800);

        setTimeout(function() {
            $('#proc-step-2').removeClass('active').addClass('done').find('i').attr('class', 'fas fa-check-circle');
            $('#proc-step-3').addClass('active').find('i').attr('class', 'fas fa-spinner fa-spin');
        }, 1600);
    }

    function hideProcessingOverlay() {
        var $overlay = $('#order-processing-overlay');
        $overlay.removeClass('show');
        setTimeout(function() { $overlay.css('display', 'none'); }, 300);
    }

    function showOverlaySuccess() {
        $('#processing-animation').html('<div class="processing-success-icon"><i class="fas fa-check-circle"></i></div>');
        $('#processing-title').text('Order Placed Successfully!');
        $('#processing-msg').text('Redirecting to your order details...');
        $('#processing-steps').hide();
    }

    function showOverlayPaymentInit() {
        $('#processing-animation').html('<div class="processing-payment-icon"><i class="fas fa-credit-card"></i></div>');
        $('#processing-title').text('Initiating Payment...');
        $('#processing-msg').text('Opening secure payment gateway...');
        $('#processing-steps').hide();
    }

    // ── Place Order ──────────────────────────────────
    $(document).on('click', '#place-order-btn', function() {
        var $btn = $(this);

        // Get delivery address
        var address = '';
        var selectedAddr = $('input[name="selected_address"]:checked');
        if (selectedAddr.length) {
            address = selectedAddr.data('address');
        } else {
            var line1 = $('#addr_line1').val();
            var city = $('#addr_city').val();
            var pincode = $('#addr_pincode').val();
            if (!line1 || !city || !pincode) {
                showToast('Please enter delivery address', 'error');
                return;
            }
            address = line1 + ', ' + ($('#addr_line2').val() ? $('#addr_line2').val() + ', ' : '') + city + ', Haryana - ' + pincode;
        }

        $btn.prop('disabled', true);
        showProcessingOverlay();

        $.ajax({
            url: SITE_URL + '/api/orders/',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                delivery_address: address,
                delivery_date: $('#delivery_date').val(),
                delivery_time_slot: $('#delivery_time').val(),
                payment_method: $('input[name="payment_method"]:checked').val(),
                notes: $('#order_notes').val()
            }),
            success: function(res) {
                if (res.success) {
                    if (res.order.payment_method === 'online') {
                        // Online payment — show payment init then open Cashfree
                        showOverlayPaymentInit();
                        setTimeout(function() {
                            hideProcessingOverlay();
                            initiateCashfree(res.order);
                        }, 1500);
                    } else {
                        // COD — show success then redirect
                        showOverlaySuccess();
                        setTimeout(function() {
                            window.location.href = SITE_URL + '/pages/order-confirmation.php?id=' + res.order.id;
                        }, 2000);
                    }
                } else {
                    hideProcessingOverlay();
                    showToast(res.message, 'error');
                }
            },
            error: function(xhr) {
                hideProcessingOverlay();
                var r = xhr.responseJSON || {};
                showToast(r.message || 'Failed to place order', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Place Order');
            }
        });
    });

    // ── Cashfree Payment ─────────────────────────────
    function initiateCashfree(order) {
        $.ajax({
            url: SITE_URL + '/api/payment/?action=create-order',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ order_id: order.id }),
            success: function(res) {
                if (!res.success) {
                    showToast(res.message || 'Payment init failed', 'error');
                    return;
                }

                cashfree.checkout({
                    paymentSessionId: res.payment_session_id,
                    redirectTarget: '_modal'
                }).then(function(result) {
                    if (result.error) {
                        showToast('Payment cancelled or failed', 'error');
                        return;
                    }
                    if (result.redirect) {
                        return;
                    }
                    if (result.paymentDetails) {
                        verifyPayment(order.id, res.cf_order_id);
                    }
                });
            },
            error: function() {
                showToast('Failed to initiate payment', 'error');
            }
        });
    }

    // ── Verify Payment ───────────────────────────────
    function verifyPayment(orderId, cfOrderId) {
        showToast('Verifying payment...', 'info');
        $.ajax({
            url: SITE_URL + '/api/payment/?action=verify',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ order_id: orderId, cf_order_id: cfOrderId }),
            success: function(res) {
                if (res.success) {
                    showToast('Payment successful!', 'success');
                    setTimeout(function() {
                        window.location.href = SITE_URL + '/pages/order-confirmation.php?id=' + orderId;
                    }, 1000);
                } else if (res.payment_status === 'pending') {
                    setTimeout(function() { verifyPayment(orderId, cfOrderId); }, 3000);
                } else {
                    showToast(res.message || 'Payment failed', 'error');
                    setTimeout(function() {
                        window.location.href = SITE_URL + '/pages/my-orders.php';
                    }, 2000);
                }
            },
            error: function() {
                showToast('Could not verify payment. Check My Orders.', 'error');
            }
        });
    }
});
</script>
