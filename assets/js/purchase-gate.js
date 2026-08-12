/* PM Dairy purchase authentication gate. Browsing remains public; purchasing requires login. */
$(document).ready(function () {
    $(document).on('click', '.btn-add-cart', function (e) {
        if (typeof IS_LOGGED_IN !== 'undefined' && IS_LOGGED_IN) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const next = window.location.pathname + window.location.search;
        const loginUrl = SITE_URL + '/pages/login.php?next=' + encodeURIComponent(next);
        showToast('Please login or sign up to buy this product.', 'info');
        setTimeout(function () {
            window.location.href = loginUrl;
        }, 450);
    });
});
