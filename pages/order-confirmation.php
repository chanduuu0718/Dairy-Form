<?php
/**
 * Order Confirmation Page
 */
require_once __DIR__ . '/../config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$orderId = intval($_GET['id'] ?? 0);
$user = Auth::getUser();
$db = Database::getInstance();

$order = $db->fetchOne(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    [$orderId, $user['id']], 'ii'
);

if (!$order) {
    header('Location: ' . SITE_URL . '/pages/my-orders.php');
    exit;
}

$orderItems = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId], 'i');

$pageTitle = 'Order Confirmed';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="order-confirmation animate-on-scroll fade-up">
            <div class="confirmation-icon">
                <div class="checkmark-circle">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h1>Order Confirmed!</h1>
            <p class="confirmation-msg">Thank you for your order. Your farm-fresh products are on their way!</p>

            <div class="order-details-card">
                <div class="order-detail-header">
                    <div>
                        <strong>Order #<?= htmlspecialchars($order['order_number']) ?></strong>
                        <span class="order-date"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div>
                        <?= getOrderStatusBadge($order['order_status']) ?>
                        <span class="payment-status-badge payment-status-<?= $order['payment_status'] ?>" style="margin-left: 0.5rem;">
                            <i class="fas <?= $order['payment_status'] === 'paid' ? 'fa-check-circle' : ($order['payment_status'] === 'pending' ? 'fa-clock' : 'fa-times-circle') ?>"></i>
                            <?= ucfirst($order['payment_status']) ?>
                        </span>
                    </div>
                </div>

                <div class="order-items-list">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-item-row">
                            <span><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?></span>
                            <span><?= formatPrice($item['total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-totals">
                    <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($order['subtotal']) ?></span></div>
                    <div class="summary-row"><span>Delivery</span><span><?= $order['delivery_charge'] > 0 ? formatPrice($order['delivery_charge']) : 'FREE' ?></span></div>
                    <div class="summary-row summary-total"><span>Total</span><span><?= formatPrice($order['total_amount']) ?></span></div>
                </div>

                <div class="order-info-grid">
                    <div>
                        <strong>Payment</strong>
                        <p><?= $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online (Cashfree)' ?></p>
                    </div>
                    <div>
                        <strong>Delivery Address</strong>
                        <p><?= htmlspecialchars($order['delivery_address']) ?></p>
                    </div>
                    <?php if ($order['delivery_date']): ?>
                        <div>
                            <strong>Delivery Date</strong>
                            <p><?= date('d M Y', strtotime($order['delivery_date'])) ?> <?= htmlspecialchars($order['delivery_time_slot']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="<?= SITE_URL ?>/pages/my-orders.php" class="btn btn-primary"><i class="fas fa-box"></i> View My Orders</a>
                <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-primary"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
