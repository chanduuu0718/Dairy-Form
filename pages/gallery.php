<?php
/**
 * Gallery Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Gallery';
$pageDescription = 'Browse photos and videos from PM Dairy Farm - our cattle, products, farm infrastructure, and team.';

$db = Database::getInstance();
$photos = $db->fetchAll("SELECT * FROM gallery WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");

// Group by category
$categories = [];
foreach ($photos as $photo) {
    $categories[$photo['category']][] = $photo;
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Gallery</span>
            </nav>
            <h1>Photo Gallery</h1>
            <p>A visual journey through our beautiful dairy farm</p>
        </div>
    </div>
</section>

<!-- Gallery -->
<section class="section">
    <div class="container">
        <!-- Category Filter -->
        <div class="gallery-filters animate-on-scroll fade-up">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="farm">Farm</button>
            <button class="filter-btn" data-filter="cattle">Cattle</button>
            <button class="filter-btn" data-filter="products">Products</button>
            <button class="filter-btn" data-filter="team">Team</button>
            <button class="filter-btn" data-filter="events">Events</button>
        </div>

        <!-- Photo Grid -->
        <div class="gallery-masonry" id="gallery-grid">
            <?php if (empty($photos)): ?>
                <!-- Placeholder gallery items -->
                <?php
                $placeholders = [
                    ['cat' => 'farm', 'title' => 'Our Green Pastures', 'img' => 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=400&q=80'],
                    ['cat' => 'cattle', 'title' => 'Gir Cows Grazing', 'img' => 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?w=400&q=80'],
                    ['cat' => 'products', 'title' => 'Fresh Farm Milk', 'img' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400&q=80'],
                    ['cat' => 'farm', 'title' => 'Morning at the Farm', 'img' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&q=80'],
                    ['cat' => 'cattle', 'title' => 'Sahiwal Breed', 'img' => 'https://images.unsplash.com/photo-1527153857715-3908f2bae5e8?w=400&q=80'],
                    ['cat' => 'products', 'title' => 'Pure Desi Ghee', 'img' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&q=80'],
                    ['cat' => 'team', 'title' => 'Our Dedicated Team', 'img' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&q=80'],
                    ['cat' => 'farm', 'title' => 'Cattle Shed', 'img' => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=400&q=80'],
                    ['cat' => 'events', 'title' => 'Farm Visit Day', 'img' => 'https://images.unsplash.com/photo-1594761051656-50916cef7c76?w=400&q=80'],
                    ['cat' => 'products', 'title' => 'Fresh Paneer', 'img' => 'https://images.unsplash.com/photo-1631452180539-96aca7d48617?w=400&q=80'],
                    ['cat' => 'cattle', 'title' => 'Murrah Buffalo', 'img' => 'https://images.unsplash.com/photo-1564466809058-bf4114d55352?w=400&q=80'],
                    ['cat' => 'farm', 'title' => 'Sunset at PM Dairy', 'img' => 'https://images.unsplash.com/photo-1504173010664-32509aeebb62?w=400&q=80'],
                ];
                foreach ($placeholders as $ph):
                ?>
                    <div class="gallery-item" data-category="<?= $ph['cat'] ?>">
                        <a href="<?= $ph['img'] ?>" data-lightbox="<?= $ph['img'] ?>" data-title="<?= $ph['title'] ?>">
                            <img src="<?= $ph['img'] ?>" alt="<?= $ph['title'] ?>" loading="lazy">
                            <div class="gallery-item-overlay">
                                <span class="gallery-item-title"><?= $ph['title'] ?></span>
                                <span class="gallery-item-cat"><?= ucfirst($ph['cat']) ?></span>
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($photos as $photo): ?>
                    <div class="gallery-item" data-category="<?= $photo['category'] ?>">
                        <a href="<?= SITE_URL . $photo['image'] ?>" data-lightbox="<?= SITE_URL . $photo['image'] ?>" data-title="<?= htmlspecialchars($photo['title']) ?>">
                            <img src="<?= SITE_URL . $photo['image'] ?>" alt="<?= htmlspecialchars($photo['title']) ?>" loading="lazy">
                            <div class="gallery-item-overlay">
                                <span class="gallery-item-title"><?= htmlspecialchars($photo['title']) ?></span>
                                <span class="gallery-item-cat"><?= ucfirst($photo['category']) ?></span>
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Video Gallery -->
<section class="section section-cream">
    <div class="container">
        <div class="section-header animate-on-scroll fade-up">
            <span class="section-tag">Videos</span>
            <h2 class="section-title">Video Gallery</h2>
        </div>
        <div class="video-grid">
            <div class="video-card animate-on-scroll fade-up">
                <div class="video-placeholder" data-video="">
                    <i class="fas fa-play-circle"></i>
                    <p>Farm Tour Video</p>
                </div>
            </div>
            <div class="video-card animate-on-scroll fade-up">
                <div class="video-placeholder" data-video="">
                    <i class="fas fa-play-circle"></i>
                    <p>Milking Process</p>
                </div>
            </div>
            <div class="video-card animate-on-scroll fade-up">
                <div class="video-placeholder" data-video="">
                    <i class="fas fa-play-circle"></i>
                    <p>Making Desi Ghee</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Gallery filter
    $('.filter-btn').on('click', function() {
        const filter = $(this).data('filter');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        if (filter === 'all') {
            $('.gallery-item').fadeIn(300);
        } else {
            $('.gallery-item').hide();
            $(`.gallery-item[data-category="${filter}"]`).fadeIn(300);
        }
    });
});
</script>
