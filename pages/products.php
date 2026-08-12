<?php
/**
 * Products Page - PM Dairy Farm
 */
$pageTitle = 'Our Products';
$pageDescription = 'Browse PM Dairy\'s range of farm-fresh dairy products - A2 milk, desi ghee, paneer, dahi, chhach, cream, khoya, butter and more.';

require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();

// ---- Read query params for initial server-side load ----
$activeCategory = sanitize($_GET['category'] ?? '');
$activeSort      = sanitize($_GET['sort'] ?? 'newest');
$currentPage     = max(1, intval($_GET['page'] ?? 1));
$searchQuery     = sanitize($_GET['search'] ?? '');

// ---- Build WHERE clause ----
$where  = ["p.is_available = 1"];
$params = [];
$types  = '';

if (!empty($activeCategory)) {
    $where[] = "c.slug = ?";
    $params[] = $activeCategory;
    $types  .= 's';
}

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    $term     = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types   .= 'sss';
}

$whereClause = implode(' AND ', $where);

// ---- Count total for pagination ----
$countRow = $db->fetchOne(
    "SELECT COUNT(*) as total FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause",
    $params,
    $types
);
$totalProducts = $countRow['total'];
$pagination    = getPagination($totalProducts, $currentPage, ITEMS_PER_PAGE);

// ---- Sort ----
$orderBy = match ($activeSort) {
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'popular'    => 'p.is_featured DESC, p.id DESC',
    default      => 'p.created_at DESC',
};

// ---- Fetch products ----
$offset   = $pagination['offset'];
$limit    = ITEMS_PER_PAGE;
$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE $whereClause
     ORDER BY $orderBy
     LIMIT $limit OFFSET $offset",
    $params,
    $types
);

foreach ($products as &$product) {
    $product['images']         = json_decode($product['images'] ?? '[]', true) ?: [];
    $product['nutrition_info'] = json_decode($product['nutrition_info'] ?? '{}', true) ?: [];
}
unset($product);

// ---- Fetch categories for filter bar ----
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");

require_once INCLUDES_PATH . 'header.php';
?>

<!-- ==================== HERO / BREADCRUMB ==================== -->
<section class="page-hero">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content animate-on-scroll fade-up">
            <h1 class="page-hero-title">Our Products</h1>
            <p class="page-hero-subtitle">Pure, fresh dairy products crafted with love - delivered from our farm to your doorstep</p>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ul class="breadcrumb-list">
                    <li><a href="<?= SITE_URL ?>/">Home</a></li>
                    <?php if (!empty($activeCategory)): ?>
                        <li><a href="<?= SITE_URL ?>/pages/products.php">Products</a></li>
                        <li class="active"><?= getCategoryName($activeCategory) ?></li>
                    <?php else: ?>
                        <li class="active">Products</li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</section>

<!-- ==================== PRODUCTS SECTION ==================== -->
<section class="section" id="products-section">
    <div class="container">

        <!-- ---- Toolbar: Categories + Sort + Search ---- -->
        <div class="products-toolbar animate-on-scroll fade-up">
            <!-- Category Filter Bar -->
            <div class="category-filter-bar" id="category-filter">
                <button class="category-btn <?= empty($activeCategory) ? 'active' : '' ?>" data-category="">
                    <i class="fas fa-th"></i> All
                </button>
                <button class="category-btn <?= $activeCategory === 'milk' ? 'active' : '' ?>" data-category="milk">
                    <i class="fas fa-glass-whiskey"></i> Milk
                </button>
                <button class="category-btn <?= $activeCategory === 'ghee' ? 'active' : '' ?>" data-category="ghee">
                    <i class="fas fa-fill-drip"></i> Ghee
                </button>
                <button class="category-btn <?= $activeCategory === 'paneer' ? 'active' : '' ?>" data-category="paneer">
                    <i class="fas fa-cheese"></i> Paneer
                </button>
                <button class="category-btn <?= $activeCategory === 'dahi' ? 'active' : '' ?>" data-category="dahi">
                    <i class="fas fa-ice-cream"></i> Dahi
                </button>
                <button class="category-btn <?= $activeCategory === 'chhach' ? 'active' : '' ?>" data-category="chhach">
                    <i class="fas fa-mug-hot"></i> Chhach
                </button>
                <button class="category-btn <?= $activeCategory === 'cream' ? 'active' : '' ?>" data-category="cream">
                    <i class="fas fa-cloud"></i> Cream
                </button>
                <button class="category-btn <?= $activeCategory === 'khoya' ? 'active' : '' ?>" data-category="khoya">
                    <i class="fas fa-cookie"></i> Khoya
                </button>
                <button class="category-btn <?= $activeCategory === 'butter' ? 'active' : '' ?>" data-category="butter">
                    <i class="fas fa-bread-slice"></i> Butter
                </button>
            </div>

            <!-- Sort & Search -->
            <div class="products-toolbar-right">
                <div class="search-box-inline">
                    <i class="fas fa-search"></i>
                    <input type="text"
                           id="product-search"
                           class="form-input"
                           placeholder="Search products..."
                           value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
                <div class="sort-dropdown">
                    <label for="product-sort"><i class="fas fa-sort-amount-down"></i></label>
                    <select id="product-sort" class="form-input">
                        <option value="newest" <?= $activeSort === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="popular" <?= $activeSort === 'popular' ? 'selected' : '' ?>>Popular</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Active Filters / Result Count -->
        <div class="products-meta" id="products-meta">
            <p class="products-count">
                Showing <strong><?= count($products) ?></strong> of <strong><?= $totalProducts ?></strong> products
                <?php if (!empty($activeCategory)): ?>
                    in <strong><?= getCategoryName($activeCategory) ?></strong>
                <?php endif; ?>
                <?php if (!empty($searchQuery)): ?>
                    for "<strong><?= htmlspecialchars($searchQuery) ?></strong>"
                <?php endif; ?>
            </p>
        </div>

        <!-- Loading Indicator -->
        <div class="products-loader" id="products-loader" style="display:none;">
            <div class="loader-spinner"></div>
            <p>Loading products...</p>
        </div>

        <!-- ---- Product Grid ---- -->
        <div class="products-grid" id="products-grid">
            <?php if (empty($products)): ?>
                <div class="products-empty">
                    <div class="empty-icon"><i class="fas fa-box-open"></i></div>
                    <h3>No Products Found</h3>
                    <p>We could not find any products matching your criteria. Try adjusting your filters or browse all products.</p>
                    <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card animate-on-scroll fade-up" data-category="<?= htmlspecialchars($product['category_slug']) ?>">
                        <div class="product-card-image">
                            <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
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
                                <span><?= formatPrice($product['price']) ?></span>
                                <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                                    <span class="product-compare-price"><?= formatPrice($product['compare_price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary btn-sm btn-block btn-add-cart"
                                    data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ---- Pagination ---- -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination" id="products-pagination">
            <?php if ($pagination['has_prev']): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>"
                   class="pagination-btn pagination-prev"
                   data-page="<?= $pagination['current_page'] - 1 ?>">
                    <i class="fas fa-chevron-left"></i> Prev
                </a>
            <?php endif; ?>

            <div class="pagination-numbers">
                <?php
                $startPage = max(1, $pagination['current_page'] - 2);
                $endPage   = min($pagination['total_pages'], $pagination['current_page'] + 2);

                if ($startPage > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                       class="pagination-num" data-page="1">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                       class="pagination-num <?= $i === $pagination['current_page'] ? 'active' : '' ?>"
                       data-page="<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($endPage < $pagination['total_pages']): ?>
                    <?php if ($endPage < $pagination['total_pages'] - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['total_pages']])) ?>"
                       class="pagination-num" data-page="<?= $pagination['total_pages'] ?>"><?= $pagination['total_pages'] ?></a>
                <?php endif; ?>
            </div>

            <?php if ($pagination['has_next']): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>"
                   class="pagination-btn pagination-next"
                   data-page="<?= $pagination['current_page'] + 1 ?>">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ==================== PAGE-SPECIFIC STYLES ==================== -->
<style>
/* ---- Toolbar ---- */
.products-toolbar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}
.category-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}
.category-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.15rem;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    background: #fff;
    color: #555;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    white-space: nowrap;
}
.category-btn:hover {
    border-color: #52B788;
    color: #1A3C2A;
}
.category-btn.active {
    background: linear-gradient(135deg, #1A3C2A, #52B788);
    color: #FDF8F0;
    border-color: transparent;
}
.category-btn i {
    font-size: 0.8rem;
}
.products-toolbar-right {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}
.search-box-inline {
    flex: 1;
    min-width: 200px;
    position: relative;
}
.search-box-inline i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 0.9rem;
}
.search-box-inline .form-input {
    padding-left: 2.2rem;
    border-radius: 50px;
    height: 42px;
    width: 100%;
}
.sort-dropdown {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.sort-dropdown label {
    color: #888;
    font-size: 0.9rem;
}
.sort-dropdown .form-input {
    min-width: 180px;
    border-radius: 50px;
    height: 42px;
    padding: 0 1rem;
    font-size: 0.88rem;
}

/* ---- Products Meta ---- */
.products-meta {
    margin-bottom: 1.5rem;
}
.products-count {
    color: #777;
    font-size: 0.9rem;
}
.products-count strong {
    color: #1A3C2A;
}

/* ---- Products Grid ---- */
.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

/* ---- Empty State ---- */
.products-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: #f9f9f9;
    border-radius: 16px;
}
.empty-icon {
    font-size: 3.5rem;
    color: #ccc;
    margin-bottom: 1rem;
}
.products-empty h3 {
    font-family: 'Playfair Display', serif;
    color: #1A3C2A;
    margin-bottom: 0.75rem;
}
.products-empty p {
    color: #777;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* ---- Loader ---- */
.products-loader {
    text-align: center;
    padding: 3rem 0;
}
.products-loader .loader-spinner {
    margin: 0 auto 1rem;
}
.products-loader p {
    color: #888;
    font-size: 0.9rem;
}

/* ---- Pagination ---- */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 3rem;
    flex-wrap: wrap;
}
.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.6rem 1.25rem;
    border-radius: 50px;
    background: #fff;
    color: #1A3C2A;
    border: 2px solid #e0e0e0;
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}
.pagination-btn:hover {
    border-color: #52B788;
    color: #52B788;
}
.pagination-numbers {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.pagination-num {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #fff;
    color: #555;
    border: 2px solid #e0e0e0;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.25s ease;
    cursor: pointer;
}
.pagination-num:hover {
    border-color: #52B788;
    color: #52B788;
}
.pagination-num.active {
    background: linear-gradient(135deg, #1A3C2A, #52B788);
    color: #FDF8F0;
    border-color: transparent;
}
.pagination-dots {
    width: 40px;
    text-align: center;
    color: #999;
    font-weight: 600;
}

/* ---- Responsive ---- */
@media (max-width: 1199px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 767px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .products-toolbar-right {
        flex-direction: column;
        align-items: stretch;
    }
    .sort-dropdown .form-input {
        min-width: 0;
        width: 100%;
    }
    .category-filter-bar {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 0.75rem;
    }
    .category-filter-bar::-webkit-scrollbar {
        display: none;
    }
}
@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Page JS is after footer so jQuery is loaded -->

<?php
// Store variables for use after footer
$jsCategory = addslashes($activeCategory);
$jsSort = addslashes($activeSort);
$jsSearch = addslashes($searchQuery);
$jsCurrentPage = (int)$pagination['current_page'];
$jsPerPage = ITEMS_PER_PAGE;
?>

<?php require_once INCLUDES_PATH . 'footer.php'; ?>

<script>
$(document).ready(function () {

    // ---- State ----
    var currentCategory = '<?= $jsCategory ?>';
    var currentSort     = '<?= $jsSort ?>';
    var currentSearch   = '<?= $jsSearch ?>';
    var currentPage     = <?= $jsCurrentPage ?>;
    var perPage         = <?= $jsPerPage ?>;
    var isLoading       = false;

    // ---- AJAX: Load Products ----
    function loadProducts(page) {
        if (isLoading) return;
        isLoading = true;

        var $grid       = $('#products-grid');
        var $loader     = $('#products-loader');
        var $pagination = $('#products-pagination');
        var $meta       = $('#products-meta');

        $grid.css('opacity', '0.4');
        $loader.show();

        $.ajax({
            url: SITE_URL + '/api/products/',
            method: 'GET',
            data: {
                category: currentCategory,
                sort: currentSort,
                search: currentSearch,
                page: page || 1,
                limit: perPage
            },
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    $grid.html(emptyState('Something went wrong. Please try again.'));
                    $pagination.html('');
                    $meta.html('');
                    return;
                }

                currentPage = res.pagination.current_page;

                // Update URL without reload
                var params = new URLSearchParams();
                if (currentCategory) params.set('category', currentCategory);
                if (currentSort && currentSort !== 'newest') params.set('sort', currentSort);
                if (currentSearch) params.set('search', currentSearch);
                if (currentPage > 1) params.set('page', currentPage);
                var qs = params.toString();
                var newUrl = window.location.pathname + (qs ? '?' + qs : '');
                window.history.replaceState(null, '', newUrl);

                // Render products
                if (res.products.length === 0) {
                    $grid.html(emptyState('No products found matching your criteria.'));
                } else {
                    var html = '';
                    $.each(res.products, function (i, p) {
                        html += renderProductCard(p);
                    });
                    $grid.html(html);
                }

                // Render meta
                var metaHtml = 'Showing <strong>' + res.products.length + '</strong> of <strong>' + res.pagination.total + '</strong> products';
                if (currentCategory) {
                    metaHtml += ' in <strong>' + categoryLabel(currentCategory) + '</strong>';
                }
                if (currentSearch) {
                    metaHtml += ' for "<strong>' + escHtml(currentSearch) + '</strong>"';
                }
                $meta.html('<p class="products-count">' + metaHtml + '</p>');

                // Render pagination
                $pagination.length ? $pagination.replaceWith(renderPagination(res.pagination)) : $grid.after(renderPagination(res.pagination));

                // Scroll to top of products section
                $('html, body').animate({ scrollTop: $('#products-section').offset().top - 100 }, 400);
            },
            error: function () {
                $grid.html(emptyState('Failed to load products. Please check your connection.'));
            },
            complete: function () {
                $grid.css('opacity', '1');
                $loader.hide();
                isLoading = false;

                // Re-trigger scroll animations for new cards
                $('.animate-on-scroll').each(function () {
                    var el = $(this);
                    var elTop = el.offset().top;
                    var winBottom = $(window).scrollTop() + $(window).height();
                    if (elTop < winBottom - 50) {
                        el.addClass('animated');
                    }
                });
            }
        });
    }

    // ---- Render a product card ----
    function renderProductCard(p) {
        var imgSrc = (p.images && p.images.length) ? SITE_URL + p.images[0] : SITE_URL + '/assets/images/placeholder.jpg';
        var saleBadge = '';
        if (p.compare_price && parseFloat(p.compare_price) > parseFloat(p.price)) {
            var discount = Math.round((1 - p.price / p.compare_price) * 100);
            saleBadge = '<span class="product-badge badge-sale">' + discount + '% OFF</span>';
        }
        var compareHtml = '';
        if (p.compare_price && parseFloat(p.compare_price) > parseFloat(p.price)) {
            compareHtml = '<span class="product-compare-price">\u20B9' + parseFloat(p.compare_price).toFixed(2) + '</span>';
        }

        return '<div class="product-card animate-on-scroll fade-up" data-category="' + escAttr(p.category_slug) + '">' +
            '<div class="product-card-image">' +
                saleBadge +
                '<img src="' + escAttr(imgSrc) + '" alt="' + escAttr(p.name) + '" loading="lazy">' +
                '<div class="product-card-overlay">' +
                    '<a href="' + SITE_URL + '/pages/product-detail.php?slug=' + encodeURIComponent(p.slug) + '" class="btn btn-sm btn-white">View Details</a>' +
                '</div>' +
            '</div>' +
            '<div class="product-card-body">' +
                '<span class="product-category">' + escHtml(p.category_name) + '</span>' +
                '<h3 class="product-name">' +
                    '<a href="' + SITE_URL + '/pages/product-detail.php?slug=' + encodeURIComponent(p.slug) + '">' + escHtml(p.name) + '</a>' +
                '</h3>' +
                '<p class="product-weight">' + escHtml(p.weight || '') + '</p>' +
                '<div class="product-price-row">' +
                    '<span class="product-price">\u20B9' + parseFloat(p.price).toFixed(2) + '</span>' +
                    compareHtml +
                '</div>' +
                '<button class="btn btn-primary btn-sm btn-block btn-add-cart" data-product-id="' + p.id + '">' +
                    '<i class="fas fa-cart-plus"></i> Add to Cart' +
                '</button>' +
            '</div>' +
        '</div>';
    }

    // ---- Render pagination ----
    function renderPagination(pg) {
        if (pg.total_pages <= 1) return '<div class="pagination" id="products-pagination"></div>';

        var html = '<div class="pagination" id="products-pagination">';

        if (pg.has_prev) {
            html += '<a href="#" class="pagination-btn pagination-prev" data-page="' + (pg.current_page - 1) + '"><i class="fas fa-chevron-left"></i> Prev</a>';
        }

        html += '<div class="pagination-numbers">';
        var start = Math.max(1, pg.current_page - 2);
        var end   = Math.min(pg.total_pages, pg.current_page + 2);

        if (start > 1) {
            html += '<a href="#" class="pagination-num" data-page="1">1</a>';
            if (start > 2) html += '<span class="pagination-dots">...</span>';
        }
        for (var i = start; i <= end; i++) {
            html += '<a href="#" class="pagination-num ' + (i === pg.current_page ? 'active' : '') + '" data-page="' + i + '">' + i + '</a>';
        }
        if (end < pg.total_pages) {
            if (end < pg.total_pages - 1) html += '<span class="pagination-dots">...</span>';
            html += '<a href="#" class="pagination-num" data-page="' + pg.total_pages + '">' + pg.total_pages + '</a>';
        }
        html += '</div>';

        if (pg.has_next) {
            html += '<a href="#" class="pagination-btn pagination-next" data-page="' + (pg.current_page + 1) + '">Next <i class="fas fa-chevron-right"></i></a>';
        }

        html += '</div>';
        return html;
    }

    // ---- Empty state HTML ----
    function emptyState(message) {
        return '<div class="products-empty">' +
            '<div class="empty-icon"><i class="fas fa-box-open"></i></div>' +
            '<h3>No Products Found</h3>' +
            '<p>' + message + '</p>' +
            '<a href="' + SITE_URL + '/pages/products.php" class="btn btn-primary">View All Products</a>' +
        '</div>';
    }

    // ---- Category label map ----
    function categoryLabel(slug) {
        var map = {
            'milk': 'Milk', 'ghee': 'Ghee', 'paneer': 'Paneer',
            'dahi': 'Dahi', 'chhach': 'Chhach', 'cream': 'Cream',
            'khoya': 'Khoya', 'butter': 'Butter'
        };
        return map[slug] || slug;
    }

    // ---- Helpers ----
    function escHtml(s) {
        if (!s) return '';
        return $('<div>').text(s).html();
    }
    function escAttr(s) {
        if (!s) return '';
        return $('<div>').text(s).html();
    }

    // ===================================================
    // EVENT HANDLERS
    // ===================================================

    // ---- Category filter click ----
    $(document).on('click', '.category-btn', function (e) {
        e.preventDefault();
        var cat = $(this).data('category');
        if (typeof cat === 'undefined') cat = '';
        cat = String(cat);
        if (cat === currentCategory && !isLoading) return;

        currentCategory = cat;
        currentPage = 1;

        // Update active state
        $('.category-btn').removeClass('active');
        $(this).addClass('active');

        loadProducts(1);
    });

    // ---- Sort change ----
    $('#product-sort').on('change', function () {
        currentSort = $(this).val();
        currentPage = 1;
        loadProducts(1);
    });

    // ---- Search input (debounced) ----
    var searchTimer;
    $('#product-search').on('input', function () {
        clearTimeout(searchTimer);
        var val = $(this).val().trim();
        searchTimer = setTimeout(function () {
            currentSearch = val;
            currentPage = 1;
            loadProducts(1);
        }, 400);
    });

    // ---- Pagination click ----
    $(document).on('click', '.pagination-num, .pagination-btn', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page && page !== currentPage) {
            loadProducts(page);
        }
    });
});
</script>
