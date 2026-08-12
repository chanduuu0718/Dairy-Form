<?php
/**
 * Blog Listing Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Blog';
$pageDescription = 'Read health tips, dairy knowledge, farm updates and recipes from PM Dairy Farm blog.';

$db = Database::getInstance();

$category = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));

$where = ["is_published = 1"];
$params = [];
$types = '';

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($search)) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $types .= 'ss';
}

$whereClause = implode(' AND ', $where);
$countRow = $db->fetchOne("SELECT COUNT(*) as total FROM blogs WHERE $whereClause", $params, $types);
$pagination = getPagination($countRow['total'], $page);

$blogs = $db->fetchAll(
    "SELECT id, title, slug, excerpt, featured_image, category, author, views, published_at
     FROM blogs WHERE $whereClause ORDER BY published_at DESC
     LIMIT " . ITEMS_PER_PAGE . " OFFSET {$pagination['offset']}",
    $params, $types
);

$blogCategories = $db->fetchAll("SELECT category, COUNT(*) as count FROM blogs WHERE is_published = 1 GROUP BY category");

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Blog</span>
            </nav>
            <h1>Our Blog</h1>
            <p>Health tips, dairy knowledge, farm updates & delicious recipes</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-layout">
            <!-- Blog Posts -->
            <div class="blog-main">
                <?php if (empty($blogs)): ?>
                    <div class="empty-state">
                        <i class="fas fa-newspaper"></i>
                        <h3>No posts found</h3>
                        <p>Check back soon for new articles!</p>
                    </div>
                <?php else: ?>
                    <div class="blog-grid">
                        <?php foreach ($blogs as $blog): ?>
                            <article class="blog-card animate-on-scroll fade-up">
                                <div class="blog-card-image">
                                    <a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $blog['slug'] ?>">
                                        <img src="<?= $blog['featured_image'] ? SITE_URL . $blog['featured_image'] : 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400&q=80' ?>"
                                             alt="<?= htmlspecialchars($blog['title']) ?>" loading="lazy">
                                    </a>
                                    <span class="blog-category-badge"><?= getBlogCategoryName($blog['category']) ?></span>
                                </div>
                                <div class="blog-card-body">
                                    <div class="blog-meta">
                                        <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($blog['published_at'])) ?></span>
                                        <span><i class="fas fa-eye"></i> <?= $blog['views'] ?> views</span>
                                    </div>
                                    <h3 class="blog-title">
                                        <a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $blog['slug'] ?>"><?= htmlspecialchars($blog['title']) ?></a>
                                    </h3>
                                    <p class="blog-excerpt"><?= htmlspecialchars(truncateText($blog['excerpt'] ?: strip_tags($blog['title']), 120)) ?></p>
                                    <a href="<?= SITE_URL ?>/pages/blog-detail.php?slug=<?= $blog['slug'] ?>" class="blog-read-more">
                                        Read More <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="pagination">
                            <?php if ($pagination['has_prev']): ?>
                                <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $category ? '&category=' . $category : '' ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <a href="?page=<?= $i ?><?= $category ? '&category=' . $category : '' ?>"
                                   class="page-btn <?= $i === $pagination['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $category ? '&category=' . $category : '' ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Search -->
                <div class="sidebar-widget animate-on-scroll fade-up">
                    <h4 class="widget-title">Search</h4>
                    <form action="" method="GET" class="search-form">
                        <input type="text" name="search" class="form-input" placeholder="Search articles..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <!-- Categories -->
                <div class="sidebar-widget animate-on-scroll fade-up">
                    <h4 class="widget-title">Categories</h4>
                    <ul class="category-list">
                        <li class="<?= empty($category) ? 'active' : '' ?>">
                            <a href="<?= SITE_URL ?>/pages/blog.php">All Posts</a>
                        </li>
                        <?php foreach ($blogCategories as $cat): ?>
                            <li class="<?= $category === $cat['category'] ? 'active' : '' ?>">
                                <a href="?category=<?= $cat['category'] ?>">
                                    <?= getBlogCategoryName($cat['category']) ?>
                                    <span class="count">(<?= $cat['count'] ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- WhatsApp CTA -->
                <div class="sidebar-widget sidebar-cta animate-on-scroll fade-up">
                    <h4>Have Questions?</h4>
                    <p>Chat with us on WhatsApp for instant answers about our products and services.</p>
                    <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" class="btn btn-success btn-block">
                        <i class="fab fa-whatsapp"></i> Chat Now
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
