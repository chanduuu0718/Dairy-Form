<?php
/**
 * Our Cattle / Breeds Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Our Cattle & Breeds';
$pageDescription = 'Meet our cattle - Gir, Sahiwal, HF, Jersey cows and Murrah buffaloes at PM Dairy Farm. Learn about each breed and their milk benefits.';

$db = Database::getInstance();
$breeds = $db->fetchAll("SELECT * FROM cattle_breeds WHERE is_active = 1 ORDER BY sort_order ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Our Cattle</span>
            </nav>
            <h1>Our Cattle & Breeds</h1>
            <p>Meet the heart of PM Dairy — our well-cared-for cows and buffaloes</p>
        </div>
    </div>
</section>

<!-- Intro -->
<section class="section">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <h2 class="section-title">The Finest Dairy Breeds</h2>
            <p class="section-subtitle">At PM Dairy, we raise the finest indigenous and cross-bred cattle. Each breed is chosen for its unique milk quality, health benefits, and adaptability to our local climate.</p>
        </div>

        <!-- Breed Cards -->
        <div class="breeds-grid">
            <?php foreach ($breeds as $index => $breed): ?>
                <div class="breed-card animate-on-scroll <?= $index % 2 === 0 ? 'slide-left' : 'slide-right' ?>">
                    <div class="breed-card-image">
                        <img src="<?= $breed['image'] ? SITE_URL . $breed['image'] : 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?w=500&q=80' ?>"
                             alt="<?= htmlspecialchars($breed['name']) ?>" loading="lazy">
                        <div class="breed-milk-badge">
                            <i class="fas fa-tint"></i> <?= htmlspecialchars($breed['daily_milk_capacity']) ?> / day
                        </div>
                    </div>
                    <div class="breed-card-body">
                        <div class="breed-header">
                            <h3><?= htmlspecialchars($breed['name']) ?></h3>
                            <span class="breed-origin"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($breed['origin']) ?></span>
                        </div>

                        <p class="breed-specialty"><?= htmlspecialchars($breed['specialty']) ?></p>

                        <div class="breed-fact">
                            <div class="breed-fact-icon">💡</div>
                            <div>
                                <strong>Fun Fact</strong>
                                <p><?= htmlspecialchars($breed['fun_fact']) ?></p>
                            </div>
                        </div>

                        <div class="breed-health">
                            <div class="breed-health-icon">🏥</div>
                            <div>
                                <strong>Health Benefits</strong>
                                <p><?= htmlspecialchars($breed['health_benefits']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content animate-on-scroll fade-up">
            <h2 class="cta-title">Want to Meet Our Cattle?</h2>
            <p class="cta-text">Book a farm visit and experience the joy of interacting with our happy cattle in person.</p>
            <a href="<?= SITE_URL ?>/pages/farm-visit.php" class="btn btn-gold btn-lg">
                <i class="fas fa-calendar-check"></i> Book Farm Visit
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
