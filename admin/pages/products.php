<?php
/**
 * Admin: Products Management
 */
$adminTitle = 'Products';
require_once __DIR__ . '/../includes/header.php';

// Fetch products
$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p
     JOIN categories c ON p.category_id = c.id
     ORDER BY p.created_at DESC"
);

// Parse images for each product
foreach ($products as &$product) {
    $product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
}
unset($product);

// Fetch categories for the form
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="admin-page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
    <h1 class="admin-page-title" style="margin:0;">Products Management</h1>
    <button class="btn btn-primary" id="btn-add-product">
        <i class="fas fa-plus"></i> Add Product
    </button>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Products (<?= count($products) ?>)</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="8" class="text-center">No products found</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr id="product-row-<?= $product['id'] ?>">
                            <td>
                                <?php if (!empty($product['images'])): ?>
                                    <img src="<?= SITE_URL . $product['images'][0] ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                                         style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:50px; height:50px; background:#f0f0f0; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                <?php if (!empty($product['weight'])): ?>
                                    <br><small class="text-muted"><?= $product['weight'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($product['category_name']) ?></span></td>
                            <td>
                                <strong><?= formatPrice($product['price']) ?></strong>
                                <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                                    <br><small class="text-muted" style="text-decoration:line-through;"><?= formatPrice($product['compare_price']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $product['stock'] > 10 ? 'badge-success' : ($product['stock'] > 0 ? 'badge-warning' : 'badge-danger') ?>">
                                    <?= $product['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="toggle-featured" data-id="<?= $product['id'] ?>" <?= $product['is_featured'] ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="toggle-active" data-id="<?= $product['id'] ?>" <?= $product['is_available'] ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <button class="btn btn-sm btn-outline-primary btn-edit-product"
                                            data-id="<?= $product['id'] ?>"
                                            data-name="<?= htmlspecialchars($product['name']) ?>"
                                            data-category="<?= $product['category_id'] ?>"
                                            data-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                                            data-short-description="<?= htmlspecialchars($product['short_description'] ?? '') ?>"
                                            data-price="<?= $product['price'] ?>"
                                            data-compare-price="<?= $product['compare_price'] ?? '' ?>"
                                            data-unit="<?= $product['unit'] ?>"
                                            data-weight="<?= htmlspecialchars($product['weight'] ?? '') ?>"
                                            data-stock="<?= $product['stock'] ?>"
                                            data-nutrition="<?= htmlspecialchars($product['nutrition_info'] ?? '') ?>"
                                            data-featured="<?= $product['is_featured'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-product" data-id="<?= $product['id'] ?>" data-name="<?= htmlspecialchars($product['name']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Form -->
<div class="admin-card mt-4" id="product-form-card" style="display:none;">
    <div class="admin-card-header">
        <h2 id="product-form-title">Add New Product</h2>
        <button class="btn btn-sm btn-outline-secondary" id="btn-cancel-product">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
    <form id="product-form" method="post" enctype="multipart/form-data" style="padding:20px;">
        <input type="hidden" id="product-id" name="product_id" value="">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" id="p-name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" id="p-category" class="form-input form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" id="p-description" class="form-input" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Short Description</label>
            <input type="text" name="short_description" id="p-short-description" class="form-input" maxlength="255">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:20px;">
            <div class="form-group">
                <label class="form-label">Price (INR) *</label>
                <input type="number" name="price" id="p-price" class="form-input" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Compare Price</label>
                <input type="number" name="compare_price" id="p-compare-price" class="form-input" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Unit</label>
                <select name="unit" id="p-unit" class="form-input form-select">
                    <option value="kg">Kg</option>
                    <option value="ltr">Ltr</option>
                    <option value="pcs">Pcs</option>
                    <option value="pack">Pack</option>
                    <option value="gm">Gm</option>
                    <option value="ml">ML</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Weight</label>
                <input type="text" name="weight" id="p-weight" class="form-input" placeholder="e.g. 500g, 1L">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
                <label class="form-label">Stock *</label>
                <input type="number" name="stock" id="p-stock" class="form-input" min="0" required>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:15px; padding-top:25px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="is_featured" id="p-featured" value="1">
                    <span>Featured Product</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Nutrition Info (JSON or plain text)</label>
            <textarea name="nutrition_info" id="p-nutrition" class="form-input" rows="3" placeholder='{"calories":"120","protein":"8g","fat":"6g"}'></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Product Images (max 5)</label>
            <input type="file" name="images[]" id="p-images" class="form-input" multiple accept="image/*">
            <small class="text-muted">Supported: JPG, PNG, WebP, JPG, GIF. Max 5MB each.</small>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary" id="btn-save-product">
                <i class="fas fa-save"></i> Save Product
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-cancel-product-2">Cancel</button>
        </div>
    </form>
</div>

<style>
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    border-radius: 24px;
    transition: 0.3s;
}
.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}
.toggle-switch input:checked + .toggle-slider {
    background-color: #27ae60;
}
.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(20px);
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const $formCard = $('#product-form-card');
    const $form = $('#product-form');

    // Show Add Product form
    $('#btn-add-product').on('click', function() {
        $form[0].reset();
        $('#product-id').val('');
        $('#product-form-title').text('Add New Product');
        $formCard.slideDown();
        $('html, body').animate({ scrollTop: $formCard.offset().top - 80 }, 300);
    });

    // Cancel form
    $('#btn-cancel-product, #btn-cancel-product-2').on('click', function() {
        $formCard.slideUp();
        $form[0].reset();
        $('#product-id').val('');
    });

    // Edit product
    $('.btn-edit-product').on('click', function() {
        const $btn = $(this);
        $('#product-id').val($btn.data('id'));
        $('#p-name').val($btn.data('name'));
        $('#p-category').val($btn.data('category'));
        $('#p-description').val($btn.data('description'));
        $('#p-short-description').val($btn.data('short-description'));
        $('#p-price').val($btn.data('price'));
        $('#p-compare-price').val($btn.data('compare-price'));
        $('#p-unit').val($btn.data('unit'));
        $('#p-weight').val($btn.data('weight'));
        $('#p-stock').val($btn.data('stock'));
        $('#p-nutrition').val($btn.data('nutrition'));
        $('#p-featured').prop('checked', $btn.data('featured') == 1);
        $('#product-form-title').text('Edit Product');
        $formCard.slideDown();
        $('html, body').animate({ scrollTop: $formCard.offset().top - 80 }, 300);
    });

    // Submit product form
    $form.on('submit', function(e) {
        e.preventDefault();

        const productId = $('#product-id').val();
        const formData = new FormData(this);

        // IMAGE SIZE CHECK
        const files = $('#p-images')[0].files;

        for (let i = 0; i < files.length; i++) {
            if (files[i].size > 5 * 1024 * 1024) {
                showToast("Image must be less than 5MB", "error");
                return; // STOP submit
            }
        }

        // Checkbox handling
        if (!$('#p-featured').is(':checked')) {
            formData.set('is_featured', '0');
        }

        let url, method;
        if (productId) {
            // Update existing product via PUT (send as POST with fields)
            url = SITE_URL + '/api/products/manage.php?id=' + productId;
    
            $.ajax({
                url: url,
                method: 'POST',   // IMPORTANT CHANGE
                data: formData,
                processData: false,
                contentType: false,
                headers: {'X-HTTP-Method-Override': 'PUT'},
                success: function(res) {
                    if (res.success) {
                        showToast('Product updated successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(res.message || 'Failed to update product', 'error');
                    }
                },
                error: function() {
                    showToast('Failed to update product', 'error');
                }
            });
        } else {
            // Create new product
            url = SITE_URL + '/api/products/manage.php';
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToast('Product created successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(res.message || 'Failed to create product', 'error');
                    }
                },
                error: function() {
                    showToast('Failed to create product', 'error');
                }
            });
        }
    });

    // Toggle featured
    $('.toggle-featured').on('change', function() {
        const productId = $(this).data('id');
        const isFeatured = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: SITE_URL + '/api/products/manage.php?id=' + productId,
            method: 'POST',
            headers: {'X-HTTP-Method-Override': 'PUT'},
            data: 'is_featured=' + isFeatured,
            contentType: 'application/x-www-form-urlencoded',
            success: function(res) {
                showToast(res.success ? 'Featured status updated' : (res.message || 'Update failed'), res.success ? 'success' : 'error');
            },
            error: function() {
                showToast('Failed to update featured status', 'error');
            }
        });
    });

    // Toggle active/available
    $('.toggle-active').on('change', function() {
        const productId = $(this).data('id');
        const isAvailable = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: SITE_URL + '/api/products/manage.php?id=' + productId,
            method: 'PUT',
            data: 'is_available=' + isAvailable,
            contentType: 'application/x-www-form-urlencoded',
            success: function(res) {
                showToast(res.success ? 'Product availability updated' : (res.message || 'Update failed'), res.success ? 'success' : 'error');
            },
            error: function() {
                showToast('Failed to update availability', 'error');
            }
        });
    });

    // Delete product
    $('.btn-delete-product').on('click', function() {
        const productId = $(this).data('id');
        const productName = $(this).data('name');

        if (!confirm('Are you sure you want to delete "' + productName + '"? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: SITE_URL + '/api/products/manage.php?id=' + productId,
            method: 'DELETE',
            success: function(res) {
                if (res.success) {
                    showToast('Product deleted successfully', 'success');
                    $('#product-row-' + productId).fadeOut(300, function() { $(this).remove(); });
                } else {
                    showToast(res.message || 'Failed to delete product', 'error');
                }
            },
            error: function() {
                showToast('Failed to delete product', 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
