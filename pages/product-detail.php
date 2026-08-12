<?php
/**
 * Product Detail Page
 */
require_once __DIR__ . '/../config/config.php';

$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/pages/products.php');
    exit;
}

$db = Database::getInstance();

$product = $db->fetchOne(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p JOIN categories c ON p.category_id = c.id
     WHERE p.slug = ? AND p.is_available = 1",
    [$slug], 's'
);

if (!$product) {
    header('Location: ' . SITE_URL . '/pages/products.php');
    exit;
}

$product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
$product['nutrition_info'] = json_decode($product['nutrition_info'] ?? '{}', true) ?: [];

$related = $db->fetchAll(
    "SELECT p.*, c.name as category_name FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.category_id = ? AND p.id != ? AND p.is_available = 1
     ORDER BY RAND() LIMIT 4",
    [$product['category_id'], $product['id']], 'ii'
);
foreach ($related as &$r) {
    $r['images'] = json_decode($r['images'] ?? '[]', true) ?: [];
}

$pageTitle = $product['name'];
$pageDescription = $product['short_description'] ?: truncateText(strip_tags($product['description']), 160);
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a>
            <span>/</span>
            <a href="<?= SITE_URL ?>/pages/products.php">Products</a>
            <span>/</span>
            <a href="<?= SITE_URL ?>/pages/products.php?category=<?= $product['category_slug'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
            <span>/</span>
            <span class="current"><?= htmlspecialchars($product['name']) ?></span>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="section">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Product Images -->
            <div class="product-gallery animate-on-scroll slide-left">
                <div class="gallery-main">
                    <img id="main-product-image"
                         src="<?= !empty($product['images']) ? SITE_URL . $product['images'][0] : ASSETS_URL . '/images/placeholder.jpg' ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                        <span class="product-badge badge-sale">
                            <?= round((1 - $product['price'] / $product['compare_price']) * 100) ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (count($product['images']) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($product['images'] as $i => $img): ?>
                            <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                                 onclick="$('#main-product-image').attr('src', '<?= SITE_URL . $img ?>'); $('.gallery-thumb').removeClass('active'); $(this).addClass('active');">
                                <img src="<?= SITE_URL . $img ?>" alt="<?= htmlspecialchars($product['name']) ?> - Image <?= $i + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-detail-info animate-on-scroll slide-right">
                <span class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></span>
                <h1 class="product-detail-name"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-detail-price">
                    <span class="price-current"><?= formatPrice($product['price']) ?></span>
                    <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                        <span class="price-compare"><?= formatPrice($product['compare_price']) ?></span>
                        <span class="price-save">Save <?= formatPrice($product['compare_price'] - $product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <p class="product-detail-weight">
                    <i class="fas fa-weight-hanging"></i> <?= htmlspecialchars($product['weight']) ?> | Per <?= htmlspecialchars($product['unit']) ?>
                </p>

                <div class="product-detail-short-desc">
                    <p><?= htmlspecialchars($product['short_description']) ?></p>
                </div>

                <div class="product-detail-stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="stock-badge in-stock"><i class="fas fa-check-circle"></i> In Stock</span>
                    <?php else: ?>
                        <span class="stock-badge out-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>

                <!-- Quantity & Add to Cart -->
                <div class="product-detail-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn qty-minus"><i class="fas fa-minus"></i></button>
                        <input type="number" class="qty-input" value="1" min="1" max="20">
                        <button class="qty-btn qty-plus"><i class="fas fa-plus"></i></button>
                    </div>
                    <button class="btn btn-primary btn-lg btn-add-cart" data-product-id="<?= $product['id'] ?>">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>

                <div class="product-detail-meta">
                    <div class="meta-item"><i class="fas fa-truck"></i> Free delivery on orders above ₹100</div>
                    <div class="meta-item"><i class="fas fa-clock"></i> Delivery by 7:00 AM next morning</div>
                    <div class="meta-item"><i class="fas fa-shield-alt"></i> 100% Pure & Fresh Guarantee</div>
                </div>

                <!-- Share -->
                <div class="product-share">
                    <span>Share:</span>
                    <a href="https://wa.me/?text=Check%20out%20<?= urlencode($product['name']) ?>%20from%20PM%20Dairy%20<?= urlencode(SITE_URL . '/pages/product-detail.php?slug=' . $product['slug']) ?>" target="_blank" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/pages/product-detail.php?slug=' . $product['slug']) ?>" target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($product['name'] . ' - PM Dairy') ?>&url=<?= urlencode(SITE_URL . '/pages/product-detail.php?slug=' . $product['slug']) ?>" target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>

        <!-- Tabs: Description / Nutrition -->
        <div class="product-tabs tabs-wrapper mt-5">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="tab-description">Description</button>
                <button class="tab-btn" data-tab="tab-nutrition">Nutrition Info</button>
            </div>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-description">
                    <div class="product-full-desc">
                        <?= $product['description'] ?>
                    </div>
                </div>
                <div class="tab-pane" id="tab-nutrition">
                    <?php if (!empty($product['nutrition_info'])): ?>
                        <table class="nutrition-table">
                            <thead>
                                <tr><th>Nutrient</th><th>Per Serving</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($product['nutrition_info'] as $key => $value): ?>
                                    <tr>
                                        <td><?= ucwords(str_replace('_', ' ', $key)) ?></td>
                                        <td><?= htmlspecialchars($value) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nutrition information not available for this product.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related)): ?>
<section class="section section-cream">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">You May Also Like</h2>
        </div>
        <div class="products-grid grid-4">
            <?php foreach ($related as $rp): ?>
                <div class="product-card animate-on-scroll fade-up">
                    <div class="product-card-image">
                        <img src="<?= !empty($rp['images']) ? SITE_URL . $rp['images'][0] : ASSETS_URL . '/images/placeholder.jpg' ?>"
                             alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy">
                        <div class="product-card-overlay">
                            <a href="<?= SITE_URL ?>/pages/product-detail.php?slug=<?= $rp['slug'] ?>" class="btn btn-sm btn-white">View Details</a>
                        </div>
                    </div>
                    <div class="product-card-body">
                        <span class="product-category"><?= htmlspecialchars($rp['category_name']) ?></span>
                        <h3 class="product-name"><a href="<?= SITE_URL ?>/pages/product-detail.php?slug=<?= $rp['slug'] ?>"><?= htmlspecialchars($rp['name']) ?></a></h3>
                        <p class="product-weight"><?= htmlspecialchars($rp['weight']) ?></p>
                        <div class="product-price-row">
                            <span class="product-price"><?= formatPrice($rp['price']) ?></span>
                        </div>
                        <button class="btn btn-primary btn-sm btn-block btn-add-cart" data-product-id="<?= $rp['id'] ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
