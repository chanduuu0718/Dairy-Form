<?php
/**
 * Subscription Plans Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Subscription Plans';
$pageDescription = 'Subscribe to PM Dairy for daily, weekly, or monthly delivery of farm-fresh dairy products at your doorstep.';

$db = Database::getInstance();
$products = $db->fetchAll("SELECT id, name, price, unit, weight FROM products WHERE is_available = 1 ORDER BY category_id, name");

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Subscription Plans</span>
            </nav>
            <h1>Subscription Plans</h1>
            <p>Never run out of fresh dairy — Subscribe for daily delivery!</p>
        </div>
    </div>
</section>

<!-- Plans -->
<section class="section">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">Choose Your Plan</h2>
            <p class="section-subtitle">Select a plan, pick your products, and enjoy fresh delivery every day</p>
        </div>

        <div class="plans-grid">
            <div class="plan-card animate-on-scroll fade-up" data-plan="daily">
                <div class="plan-header">
                    <h3>Daily Plan</h3>
                    <div class="plan-price">
                        <span class="plan-currency">₹</span>
                        <span class="plan-amount">70</span>
                        <span class="plan-period">/day</span>
                    </div>
                    <p>30 days commitment</p>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> Fresh delivery every morning</li>
                    <li><i class="fas fa-check"></i> Choose products & quantity</li>
                    <li><i class="fas fa-check"></i> Doorstep delivery by 7 AM</li>
                    <li><i class="fas fa-check"></i> Pause/resume anytime</li>
                    <li><i class="fas fa-check"></i> No extra delivery charges</li>
                </ul>
                <button class="btn btn-outline-primary btn-block subscribe-btn" data-plan="daily">Subscribe Daily</button>
            </div>

            <div class="plan-card plan-popular animate-on-scroll fade-up" data-plan="weekly">
                <div class="plan-badge">Most Popular</div>
                <div class="plan-header">
                    <h3>Weekly Plan</h3>
                    <div class="plan-price">
                        <span class="plan-currency">₹</span>
                        <span class="plan-amount">450</span>
                        <span class="plan-period">/week</span>
                    </div>
                    <p>4 weeks commitment</p>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> 7 days fresh products</li>
                    <li><i class="fas fa-check"></i> Save 5% vs daily rate</li>
                    <li><i class="fas fa-check"></i> Mix & match products</li>
                    <li><i class="fas fa-check"></i> Free delivery</li>
                    <li><i class="fas fa-check"></i> Flexible scheduling</li>
                </ul>
                <button class="btn btn-primary btn-block subscribe-btn" data-plan="weekly">Subscribe Weekly</button>
            </div>

            <div class="plan-card animate-on-scroll fade-up" data-plan="monthly">
                <div class="plan-header">
                    <h3>Monthly Plan</h3>
                    <div class="plan-price">
                        <span class="plan-currency">₹</span>
                        <span class="plan-amount">1800</span>
                        <span class="plan-period">/month</span>
                    </div>
                    <p>Best value</p>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> 30 days fresh products</li>
                    <li><i class="fas fa-check"></i> Save 10% vs daily rate</li>
                    <li><i class="fas fa-check"></i> Priority delivery slot</li>
                    <li><i class="fas fa-check"></i> Free delivery</li>
                    <li><i class="fas fa-check"></i> Monthly billing</li>
                </ul>
                <button class="btn btn-outline-primary btn-block subscribe-btn" data-plan="monthly">Subscribe Monthly</button>
            </div>
        </div>

        <!-- Subscription Form Modal -->
        <div class="modal" id="subscribe-modal">
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <button class="modal-close" data-close-modal>&times;</button>
                <h2><i class="fas fa-sync"></i> Create Subscription</h2>
                <p>Selected Plan: <strong id="selected-plan-name"></strong></p>

                <form id="subscription-form" class="form mt-3">
                    <input type="hidden" id="plan_type" name="plan_type">

                    <div class="form-group">
                        <label class="form-label">Select Products</label>
                        <div class="subscription-products">
                            <?php foreach ($products as $p): ?>
                                <label class="sub-product-option">
                                    <input type="checkbox" name="products[]" value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                                    <span><?= htmlspecialchars($p['name']) ?> (<?= $p['weight'] ?>) — <?= formatPrice($p['price']) ?></span>
                                    <input type="number" class="sub-qty" value="1" min="1" max="10" style="width:60px">
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Delivery Time</label>
                        <select name="delivery_time" class="form-input form-select">
                            <option value="morning">Morning (6-8 AM)</option>
                            <option value="evening">Evening (4-6 PM)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-check-circle"></i> Confirm Subscription
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">How Subscription Works</h2>
        </div>
        <div class="process-grid">
            <div class="process-step animate-on-scroll fade-up">
                <div class="process-icon"><span>1️⃣</span></div>
                <h3>Choose a Plan</h3>
                <p>Select daily, weekly, or monthly plan based on your needs.</p>
            </div>
            <div class="process-connector"><i class="fas fa-arrow-right"></i></div>
            <div class="process-step animate-on-scroll fade-up">
                <div class="process-icon"><span>2️⃣</span></div>
                <h3>Pick Products</h3>
                <p>Select which products you want and set quantities.</p>
            </div>
            <div class="process-connector"><i class="fas fa-arrow-right"></i></div>
            <div class="process-step animate-on-scroll fade-up">
                <div class="process-icon"><span>3️⃣</span></div>
                <h3>Get Delivery</h3>
                <p>Fresh products delivered to your doorstep every morning!</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('.subscribe-btn').on('click', function() {
        if (!IS_LOGGED_IN) {
            showToast('Please login to subscribe', 'warning');
            setTimeout(() => window.location.href = SITE_URL + '/pages/login.php', 1500);
            return;
        }

        const plan = $(this).data('plan');
        $('#plan_type').val(plan);
        $('#selected-plan-name').text(plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan');
        openModal('subscribe-modal');
    });

    $('#subscription-form').on('submit', function(e) {
        e.preventDefault();

        const products = [];
        $('input[name="products[]"]:checked').each(function() {
            products.push({
                product_id: $(this).val(),
                quantity: $(this).closest('.sub-product-option').find('.sub-qty').val()
            });
        });

        if (!products.length) {
            showToast('Select at least one product', 'error');
            return;
        }

        $.ajax({
            url: SITE_URL + '/api/subscriptions/',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                plan_type: $('#plan_type').val(),
                delivery_time: $('[name="delivery_time"]').val(),
                products: products
            }),
            success: function(res) {
                if (res.success) {
                    showToast('Subscription created!', 'success');
                    closeModal('subscribe-modal');
                    setTimeout(() => window.location.href = SITE_URL + '/pages/my-subscriptions.php', 1500);
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Failed', 'error');
            }
        });
    });
});
</script>
