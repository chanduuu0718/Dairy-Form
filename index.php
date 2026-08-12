<?php
/**
 * Home Page - PM Dairy Farm
 */
$pageTitle = 'Home';
$pageDescription = 'Purvanchal Dairyfarm Dairy - Pure Milk, Pure Life. Farm fresh A2 cow milk, desi ghee, paneer, dahi and more delivered daily from our farm in Haryana.';

require_once 'config/config.php';

$db = Database::getInstance();

// Featured products
$featuredProducts = $db->fetchAll(
    "SELECT p.*, c.name as category_name FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.is_featured = 1 AND p.is_available = 1 ORDER BY p.created_at DESC LIMIT 8"
);
foreach ($featuredProducts as &$fp) {
    $fp['images'] = json_decode($fp['images'] ?? '[]', true) ?: [];
}

// Testimonials
$testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 6");

// Stats
$stats = [
    'cattle' => getSetting('total_cattle') ?: '10+',
    'production' => getSetting('daily_production') ?: '50+',
    'experience' => getSetting('years_experience') ?: '1+',
    'customers' => getSetting('happy_customers') ?: '100+'
];

require_once 'includes/header.php';  
?>

<!-- Hero Section -->
<section class="hero" id="hero">
    <div class="hero-bg">
        <div class="hero-overlay"></div>
    </div>
        <div class="container">
            <div class="hero-split">
                <div class="hero-content animate-on-scroll fade-up">
                    <span class="hero-badge">🥛 100% Pure & Natural</span>
                    <h1 class="hero-title"><?= SITE_NAME ?></h1>
                    <p class="hero-tagline"><?= SITE_TAGLINE ?></p>
                    <p class="hero-desc">Experience the purity of farm-fresh dairy products delivered straight from our hygienic farm to your doorstep every morning.</p>
                    <div class="hero-trust-points">
                        <span><i class="fas fa-leaf"></i> 100% Organic</span>
                        <span><i class="fas fa-truck"></i> Daily Delivery</span>
                        <span><i class="fas fa-certificate"></i> FSSAI Certified</span>
                    </div>
                    <div class="hero-buttons">
                        <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-gold btn-lg">
                            <i class="fas fa-shopping-bag"></i> Order Now
                        </a>
                        <a href="<?= SITE_URL ?>/pages/farm-visit.php" class="btn btn-outline-white btn-lg">
                            <i class="fas fa-tractor"></i> Visit Our Farm
                        </a>
                    </div>
                </div>
                <div class="hero-image animate-on-scroll fade-up">
                    <div class="hero-image-wrapper">
                        <img src="<?= ASSETS_URL ?>/banner.png" alt="Purvanchal Dairyfarm Dairy Farm" loading="lazy"
                            onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/error_image.png';">
                    </div>
                </div>
        </div>
    </div>
    <div class="hero-scroll-indicator">
        <a href="#stats-section"><i class="fas fa-chevron-down"></i></a>
    </div>
</section>

<!-- Stats Bar -->
<section class="stats-section" id="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card animate-on-scroll fade-up">
                <div class="stat-icon"><i class="fas fa-cow"></i></div>
                <div class="stat-number" data-count="<?= $stats['cattle'] ?>" data-suffix="+"><?= $stats['cattle'] ?></div>
                <div class="stat-label">Total Cattle</div>
            </div>
            <div class="stat-card animate-on-scroll fade-up" style="animation-delay:0.1s">
                <div class="stat-icon"><i class="fas fa-flask"></i></div>
                <div class="stat-number" data-count="<?= $stats['production'] ?>" data-suffix="+"><?= $stats['production'] ?></div>
                <div class="stat-label">Daily Litres</div>
            </div>
            <div class="stat-card animate-on-scroll fade-up" style="animation-delay:0.2s">
                <div class="stat-icon"><i class="fas fa-award"></i></div>
                <div class="stat-number" data-count="<?= $stats['experience'] ?>" data-suffix="+"><?= $stats['experience'] ?></div>
                <div class="stat-label">Years Experience</div>
            </div>
            <div class="stat-card animate-on-scroll fade-up" style="animation-delay:0.3s">
                <div class="stat-icon"><i class="fas fa-smile"></i></div>
                <div class="stat-number" data-count="<?= $stats['customers'] ?>" data-suffix="+"><?= $stats['customers'] ?></div>
                <div class="stat-label">Happy Customers</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section section-cream" id="featured-products">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">Farm Fresh Dairy Products</h2>
            <p class="section-subtitle">Handpicked products from our farm, delivered fresh to your doorstep daily</p>
        </div>

        <div class="products-carousel">
            <button class="carousel-btn carousel-prev"><i class="fas fa-chevron-left"></i></button>
            <div class="carousel-track-wrapper">
                <div class="carousel-track">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card animate-on-scroll fade-up">
                            <div class="product-card-image">
                                <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                    <span class="product-badge badge-sale">
                                        <?= round((1 - $product['price'] / $product['compare_price']) * 100) ?>% OFF
                                    </span>
                                <?php endif; ?>
                                <img src="<?= !empty($product['images']) ? SITE_URL . $product['images'][0] : ASSETS_URL . '/images/placeholder.jpg' ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     loading="lazy">
                                <div class="product-card-overlay">
                                    <a href="<?= SITE_URL ?>/pages/product-detail.php?slug=<?= $product['slug'] ?>" class="btn btn-sm btn-white">View Details</a>
                                </div>
                            </div>
                            <div class="product-card-body">
                                <span class="product-category"><?= htmlspecialchars($product['category_name']) ?></span>
                                <h3 class="product-name">
                                    <a href="<?= SITE_URL ?>/pages/product-detail.php?slug=<?= $product['slug'] ?>"><?= htmlspecialchars($product['name']) ?></a>
                                </h3>
                                <p class="product-weight"><?= htmlspecialchars($product['weight']) ?></p>
                                <div class="product-price-row">
                                    <span><b><?= formatPrice($product['price']) ?></b></span>
                                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                        <span class="product-compare-price"><?= formatPrice($product['compare_price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary btn-sm btn-block btn-add-cart" data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-btn carousel-next"><i class="fas fa-chevron-right"></i></button>
        </div>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-primary">View All Products <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Our Story -->
<section class="section" id="our-story">
    <div class="container">
        <div class="story-grid">
            <div class="story-image animate-on-scroll slide-left">
                <div class="story-image-wrapper">
                    <img src="<?= ASSETS_URL ?>/farm/story_image.jpg" alt="Purvanchal Dairyfarm Dairy Farm" loading="lazy"
                         onerror="this.onerror=null; this.src='<?= ASSETS_URL ?>/error_image.png';">
                    <div class="story-experience-badge">
                        <span class="exp-number"><?= $stats['experience'] ?></span>
                        <span class="exp-text">Years of Excellence</span>
                    </div>
                </div>
            </div>
            <div class="story-content animate-on-scroll slide-right">
                <h2 class="section-title">A Legacy of Purity Since Generations</h2>
                <p>Purvanchal Dairyfarm Dairy is a family-owned dairy farm nestled in the heart of Haryana. What started as a small family farm with just a few cows has now grown into a modern dairy facility serving thousands of families with pure, farm-fresh dairy products.</p>
                <p>We believe in the ancient Indian tradition of treating our cattle as family. Every cow and buffalo at Purvanchal Dairyfarm Dairy is cared for with love, fed organic fodder, and never given any artificial hormones or injections.</p>
                <div class="story-features">
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>100% Pure & Natural</span>
                    </div>
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>No Preservatives</span>
                    </div>
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Farm to Door Delivery</span>
                    </div>
                    <div class="story-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>FSSAI Certified</span>
                    </div>
                </div>
                <a href="<?= SITE_URL ?>/pages/about.php" class="btn btn-primary">
                    Read Our Full Story <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- How We Work -->
<section class="section section-cream" id="how-we-work">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">How We Work</h2>
            <p class="section-subtitle">From our happy cattle to your doorstep — every step is done with care and hygiene</p>
        </div>

        <div class="process-grid">
            <div class="process-step animate-on-scroll fade-up">
                <div class="process-icon">
                    <span>🐄</span>
                </div>
                <h3>Happy Cattle</h3>
                <p>Our cows and buffaloes are raised with love, fed organic fodder, and live in comfortable, clean sheds.</p>
            </div>
            <div class="process-connector"><i class="fas fa-arrow-right"></i></div>
            <div class="process-step animate-on-scroll fade-up" style="animation-delay:0.15s">
                <div class="process-icon">
                    <span>🥛</span>
                </div>
                <h3>Hygienic Milking</h3>
                <p>Milking is done using modern machines in a sanitized milking parlour, maintaining the highest hygiene standards.</p>
            </div>
            <div class="process-connector"><i class="fas fa-arrow-right"></i></div>
            <div class="process-step animate-on-scroll fade-up" style="animation-delay:0.3s">
                <div class="process-icon">
                    <span>❄️</span>
                </div>
                <h3>Chilled Storage</h3>
                <p>Milk is immediately cooled in our bulk milk chilling plant to 4°C to preserve freshness and nutrition.</p>
            </div>
            <div class="process-connector"><i class="fas fa-arrow-right"></i></div>
            <div class="process-step animate-on-scroll fade-up" style="animation-delay:0.45s">
                <div class="process-icon">
                    <span>🚚</span>
                </div>
                <h3>Fresh Delivery</h3>
                <p>Products are packed and delivered fresh to your doorstep every morning before breakfast time.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section" id="testimonials">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">What Our Customers Say</h2>
            <p class="section-subtitle">Don't just take our word for it — hear from our happy customers</p>
        </div>

        <div class="testimonials-slider">
            <div class="slider-track">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'text-gold' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"<?= htmlspecialchars($testimonial['review_text']) ?>"</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <?php if ($testimonial['customer_photo']): ?>
                                    <img src="<?= SITE_URL . $testimonial['customer_photo'] ?>" alt="<?= htmlspecialchars($testimonial['customer_name']) ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder"><?= strtoupper(substr($testimonial['customer_name'], 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($testimonial['customer_name']) ?></strong>
                                <?php if ($testimonial['customer_location']): ?>
                                    <span><?= htmlspecialchars($testimonial['customer_location']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="slider-controls">
                <button class="slider-btn slider-prev"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots">
                    <?php for ($i = 0; $i < count($testimonials); $i++): ?>
                        <button class="slider-dot <?= $i === 0 ? 'active' : '' ?>"></button>
                    <?php endfor; ?>
                </div>
                <button class="slider-btn slider-next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- Farm Visit CTA -->
<section class="cta-section" id="farm-visit-cta">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content animate-on-scroll fade-up">
            <h2 class="cta-title">Visit Our Farm</h2>
            <p class="cta-text">Come see where your food comes from. Walk through our green pastures, meet our happy cattle, and experience the joy of farm life with your family.</p>
            <a href="<?= SITE_URL ?>/pages/farm-visit.php" class="btn btn-gold btn-lg">
                <i class="fas fa-calendar-check"></i> Book a Farm Visit
            </a>
        </div>
    </div>
</section>

<!-- Subscription Plans Preview -->
<section class="section section-cream" id="subscription-preview">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">Subscription Plans</h2>
            <p class="section-subtitle">Never run out of fresh dairy — subscribe and get daily delivery at your doorstep</p>
        </div>

        <div class="plans-grid">
            <div class="plan-card animate-on-scroll fade-up">
                <div class="plan-header">
                    <h3>Daily Plan</h3>
                    <div class="plan-price">
                        <span class="plan-currency">₹</span>
                        <span class="plan-amount">70</span>
                        <span class="plan-period">/day</span>
                    </div>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> Fresh milk every morning</li>
                    <li><i class="fas fa-check"></i> Choose cow or buffalo milk</li>
                    <li><i class="fas fa-check"></i> Doorstep delivery by 7 AM</li>
                    <li><i class="fas fa-check"></i> Pause/resume anytime</li>
                    <li><i class="fas fa-check"></i> No commitment</li>
                </ul>
                <a href="<?= SITE_URL ?>/pages/subscriptions.php" class="btn btn-outline-primary btn-block">Start Daily Plan</a>
            </div>

            <div class="plan-card plan-popular animate-on-scroll fade-up" style="animation-delay:0.15s">
                <div class="plan-badge">Most Popular</div>
                <div class="plan-header">
                    <h3>Weekly Plan</h3>
                    <div class="plan-price">
                        <span class="plan-currency">₹</span>
                        <span class="plan-amount">450</span>
                        <span class="plan-period">/week</span>
                    </div>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> 7 days fresh milk</li>
                    <li><i class="fas fa-check"></i> Add paneer or dahi</li>
                    <li><i class="fas fa-check"></i> Save 5% vs daily rate</li>
                    <li><i class="fas fa-check"></i> Free delivery</li>
                    <li><i class="fas fa-check"></i> Flexible scheduling</li>
                </ul>
                <a href="<?= SITE_URL ?>/pages/subscriptions.php" class="btn btn-primary btn-block">Start Weekly Plan</a>
            </div>

            <div class="plan-card animate-on-scroll fade-up" style="animation-delay:0.3s">
                <div class="plan-header">
                    <h3>Monthly Plan</h3>
                    <div class="plan-price">
                        <span class="plan-currency">₹</span>
                        <span class="plan-amount">1800</span>
                        <span class="plan-period">/month</span>
                    </div>
                </div>
                <ul class="plan-features">
                    <li><i class="fas fa-check"></i> 30 days fresh milk</li>
                    <li><i class="fas fa-check"></i> Add any dairy product</li>
                    <li><i class="fas fa-check"></i> Save 10% vs daily rate</li>
                    <li><i class="fas fa-check"></i> Priority delivery</li>
                    <li><i class="fas fa-check"></i> Monthly billing</li>
                </ul>
                <a href="<?= SITE_URL ?>/pages/subscriptions.php" class="btn btn-outline-primary btn-block">Start Monthly Plan</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
