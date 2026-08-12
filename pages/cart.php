<?php
/**
 * Shopping Cart Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Shopping Cart</span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>

        <div id="cart-container">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading cart...</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    loadCart();

    function loadCart() {
        $.get(SITE_URL + '/api/cart/', function(res) {
            if (!res.success || !res.items.length) {
                $('#cart-container').html(`
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any products yet.</p>
                        <a href="${SITE_URL}/pages/products.php" class="btn btn-primary"><i class="fas fa-shopping-basket"></i> Browse Products</a>
                    </div>
                `);
                return;
            }

            let itemsHtml = '';
            res.items.forEach(function(item) {
                const img = item.images.length ? SITE_URL + item.images[0] : SITE_URL + '/assets/images/placeholder.jpg';
                itemsHtml += `
                    <div class="cart-item" data-cart-id="${item.id}" data-product-id="${item.product_id}">
                        <div class="cart-item-image">
                            <img src="${img}" alt="${item.name}">
                        </div>
                        <div class="cart-item-info">
                            <h3><a href="${SITE_URL}/pages/product-detail.php?slug=${item.slug}">${item.name}</a></h3>
                            <p class="cart-item-weight">${item.weight || ''} | Per ${item.unit}</p>
                            <p class="cart-item-price">₹${parseFloat(item.price).toFixed(2)}</p>
                        </div>
                        <div class="cart-item-qty">
                            <div class="quantity-selector">
                                <button class="qty-btn qty-minus cart-qty-btn" data-action="decrease"><i class="fas fa-minus"></i></button>
                                <input type="number" class="qty-input cart-qty-input" value="${item.quantity}" min="1" max="20" readonly>
                                <button class="qty-btn qty-plus cart-qty-btn" data-action="increase"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="cart-item-total">
                            <span>₹${(item.price * item.quantity).toFixed(2)}</span>
                        </div>
                        <button class="cart-item-remove" title="Remove"><i class="fas fa-trash-alt"></i></button>
                    </div>
                `;
            });

            const deliveryText = res.delivery_charge > 0 ? `₹${res.delivery_charge.toFixed(2)}` : '<span class="text-success">FREE</span>';

            $('#cart-container').html(`
                <div class="cart-layout">
                    <div class="cart-items">
                        <div class="cart-header-row">
                            <span>Product</span><span></span><span>Quantity</span><span>Total</span><span></span>
                        </div>
                        ${itemsHtml}
                    </div>
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row"><span>Subtotal</span><span id="cart-subtotal">₹${res.subtotal.toFixed(2)}</span></div>
                        <div class="summary-row"><span>Delivery</span><span id="cart-delivery">${deliveryText}</span></div>
                        <div class="summary-row summary-total"><span>Total</span><span id="cart-total">₹${res.total.toFixed(2)}</span></div>
                        ${res.subtotal < res.min_order ? `<p class="cart-min-order"><i class="fas fa-info-circle"></i> Minimum order: ₹${res.min_order}</p>` : ''}
                        <a href="${SITE_URL}/pages/checkout.php" class="btn btn-primary btn-lg btn-block ${res.subtotal < res.min_order ? 'disabled' : ''}">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        <a href="${SITE_URL}/pages/products.php" class="btn btn-outline-primary btn-block mt-2">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            `);
        });
    }

    // Quantity change
    $(document).on('click', '.cart-qty-btn', function() {
        const $item = $(this).closest('.cart-item');
        const cartId = $item.data('cart-id');
        const $input = $item.find('.cart-qty-input');
        let qty = parseInt($input.val());

        if ($(this).data('action') === 'increase') qty++;
        else qty = Math.max(1, qty - 1);

        $input.val(qty);

        PMCart.update(cartId, qty, function() {
            loadCart();
        });
    });

    // Remove item
    $(document).on('click', '.cart-item-remove', function() {
        const productId = $(this).closest('.cart-item').data('product-id');
        PMCart.remove(productId, function() {
            loadCart();
        });
    });
});
</script>
