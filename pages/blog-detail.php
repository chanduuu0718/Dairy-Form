<?php
/**
 * Blog Detail Page
 */
require_once __DIR__ . '/../config/config.php';

$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/pages/blog.php');
    exit;
}

$db = Database::getInstance();
$blog = $db->fetchOne("SELECT * FROM blogs WHERE slug = ? AND is_published = 1", [$slug], 's');

if (!$blog) {
    header('Location: ' . SITE_URL . '/pages/blog.php');
    exit;
}

// Increment views
$db->update("UPDATE blogs SET views = views + 1 WHERE id = ?", [$blog['id']], 'i');

$related = $db->fetchAll(
    "SELECT id, title, slug, excerpt, featured_image, category, published_at FROM blogs
     WHERE category = ? AND id != ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3",
    [$blog['category'], $blog['id']], 'si'
);

$pageTitle = $blog['meta_title'] ?: $blog['title'];
$pageDescription = $blog['meta_description'] ?: truncateText(strip_tags($blog['excerpt'] ?: $blog['content']), 160);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a><span>/</span>
            <a href="<?= SITE_URL ?>/pages/blog.php">Blog</a><span>/</span>
            <span class="current"><?= htmlspecialchars(truncateText($blog['title'], 50)) ?></span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-layout">
            <article class="blog-detail-main">
                <!-- Featured Image -->
                <?php if ($blog['featured_image']): ?>
                    <div class="blog-detail-image animate-on-scroll fade-up">
                        <img src="<?= SITE_URL . $blog['featured_image'] ?>" alt="<?= htmlspecialchars($blog['title']) ?>">
                    </div>
                <?php endif; ?>

                <!-- Meta -->
                <div class="blog-detail-meta">
                    <span class="blog-category-badge"><?= getBlogCategoryName($blog['category']) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= date('F d, Y', strtotime($blog['published_at'])) ?></span>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($blog['author']) ?></span>
                    <span><i class="fas fa-eye"></i> <?= $blog['views'] + 1 ?> views</span>
                </div>

                <!-- Title -->
                <h1 class="blog-detail-title"><?= htmlspecialchars($blog['title']) ?></h1>

                <!-- Content -->
                <div class="blog-detail-content prose">
                    <?= $blog['content'] ?>
                </div>

                <!-- Share Buttons -->
                <div class="blog-share">
                    <span>Share this article:</span>
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=<?= urlencode($blog['title'] . ' ' . SITE_URL . '/pages/blog-detail.php?slug=' . $blog['slug']) ?>" target="_blank" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/pages/blog-detail.php?slug=' . $blog['slug']) ?>" target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($blog['title']) ?>&url=<?= urlencode(SITE_URL . '/pages/blog-detail.php?slug=' . $blog['slug']) ?>" target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <!-- Related Posts -->
                <?php if (!empty($related)): ?>
                    <div class="related-posts mt-5">
                        <h3>Related Articles</h3>
                        <div class="blog-grid grid-3">
                            <?php foreach ($related as $rp): ?>
                                <article class="blog-card">
                                    <div class="blog-card-image">
                                        <a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $rp['slug'] ?>">
                                            <img src="<?= $rp['featured_image'] ? SITE_URL . $rp['featured_image'] : 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=300&q=80' ?>" alt="<?= htmlspecialchars($rp['title']) ?>" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="blog-card-body">
                                        <h3 class="blog-title"><a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $rp['slug'] ?>"><?= htmlspecialchars($rp['title']) ?></a></h3>
                                        <span class="blog-date"><?= date('M d, Y', strtotime($rp['published_at'])) ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <div class="sidebar-widget">
                    <h4 class="widget-title">Categories</h4>
                    <ul class="category-list">
                        <li><a href="<?= SITE_URL ?>/pages/blog.php?category=health-tips">Health Tips</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/blog.php?category=farm-updates">Farm Updates</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/blog.php?category=dairy-knowledge">Dairy Knowledge</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/blog.php?category=recipes">Recipes</a></li>
                    </ul>
                </div>
                <div class="sidebar-widget sidebar-cta">
                    <h4>Order Fresh Products</h4>
                    <p>Try our farm-fresh dairy products today!</p>
                    <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-primary btn-block">Shop Now</a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
