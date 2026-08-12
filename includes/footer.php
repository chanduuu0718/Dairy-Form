    </main>
    <!-- End Main Content -->

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col">
                        <a href="<?= SITE_URL ?>/" class="footer-logo">
                            <span class="logo-icon">🐄</span>
                            <div class="logo-text">
                                <span class="logo-name"><?= SITE_NAME ?></span>
                                <span class="logo-tagline"><?= SITE_TAGLINE ?></span>
                            </div>
                        </a>
                        <p class="footer-about">We are a family-owned dairy farm committed to delivering pure, fresh, and nutritious dairy products straight from our farm to your doorstep.</p>
                        <div class="footer-social">
                            <a href="javascript:void(0);" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.youtube.com/@PurvanchalDairyfarm" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                            <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-heading">Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="<?= SITE_URL ?>/">Home</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/about.php">About Us</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/products.php">Our Products</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/cattle.php">Our Cattle</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/infrastructure.php">Farm Infrastructure</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/gallery.php">Gallery</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/blog.php">Blog</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-heading">Our Products</h4>
                        <ul class="footer-links">
                            <li><a href="<?= SITE_URL ?>/pages/products.php?category=milk">Fresh Milk</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/products.php?category=ghee">Pure Desi Ghee</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/products.php?category=paneer">Fresh Paneer</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/products.php?category=dahi">Dahi (Curd)</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/products.php?category=butter">White Butter</a></li>
                            <li><a href="<?= SITE_URL ?>/pages/subscriptions.php">Subscription Plans</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4 class="footer-heading">Contact Us</h4>
                        <ul class="footer-contact">
                            <li><i class="fas fa-map-marker-alt"></i><span><?= SITE_ADDRESS ?></span></li>
                            <li><i class="fas fa-phone-alt"></i><a href="tel:<?= SITE_PHONE ?>"><?= SITE_PHONE ?></a></li>
                            <li><i class="fab fa-whatsapp"></i><a href="https://wa.me/<?= SITE_WHATSAPP ?>">WhatsApp Us</a></li>
                            <li><i class="fas fa-envelope"></i><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></li>
                            <li><i class="fas fa-clock"></i><span>Mon - Sun: 6:00 AM - 8:00 PM</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All Rights Reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="javascript:void(0);">Privacy Policy</a>
                        <a href="javascript:void(0);">Terms & Conditions</a>
                        <a href="javascript:void(0);#">Refund Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/<?= SITE_WHATSAPP ?>?text=Hi%20PM%20Dairy!%20I%20want%20to%20know%20about%20your%20products." target="_blank" class="whatsapp-float" id="whatsapp-float" aria-label="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-tooltip">Chat with us!</span>
    </a>
    <button class="back-to-top" id="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>

    <div class="modal" id="auth-modal">
        <div class="modal-backdrop"></div>
        <div class="modal-content modal-sm">
            <button class="modal-close" data-close-modal>&times;</button>
            <div id="auth-modal-body"></div>
        </div>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const track = document.querySelector(".slider-track");
    const cards = document.querySelectorAll(".testimonial-card");
    const dots = document.querySelectorAll(".slider-dot");
    const prevBtn = document.querySelector(".slider-prev");
    const nextBtn = document.querySelector(".slider-next");
    if (!track || !cards.length || !prevBtn || !nextBtn) return;
    let currentIndex = 0;
    const totalSlides = cards.length;
    function updateSlider() {
        const slideWidth = cards[0].offsetWidth;
        track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        dots.forEach((dot, i) => dot.classList.toggle("active", i === currentIndex));
    }
    nextBtn.addEventListener("click", () => { currentIndex = (currentIndex + 1) % totalSlides; updateSlider(); });
    prevBtn.addEventListener("click", () => { currentIndex = (currentIndex - 1 + totalSlides) % totalSlides; updateSlider(); });
    dots.forEach((dot, index) => dot.addEventListener("click", () => { currentIndex = index; updateSlider(); }));
    window.addEventListener("resize", updateSlider);
});
</script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        const SITE_URL = '<?= SITE_URL ?>';
        const IS_LOGGED_IN = <?= $loggedInUser ? 'true' : 'false' ?>;
        const USER_ROLE = '<?= $loggedInUser ? $loggedInUser['role'] : '' ?>';
        const CASHFREE_MODE = '<?= CASHFREE_MODE === "TEST" ? "sandbox" : "production" ?>';
    </script>
    <!-- Purchase gate loads before main.js so unauthenticated clicks are intercepted first. -->
    <script src="<?= ASSETS_URL ?>/js/purchase-gate.js"></script>
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>

    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>