/**
 * PM Dairy Farm - Main JavaScript (jQuery)
 * Handles: Navigation, Cart, AJAX, Animations, UI
 */

// ==========================================
// PAGE LOADER - Must be outside document.ready
// to avoid missing the window load event
// ==========================================
$(window).on('load', function () {
    $('#page-loader').fadeOut(400);
});
// Fallback: hide loader after 3s even if load event is missed
setTimeout(function () { $('#page-loader').fadeOut(400); }, 3000);

$(document).ready(function () {
    const isSmallScreen = window.matchMedia('(max-width: 768px)').matches;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ==========================================
    // STICKY HEADER
    // ==========================================
    const $header = $('#main-nav');
    let lastScrollTop = 0;

    $(window).on('scroll', function () {
        const scrollTop = $(this).scrollTop();

        if (scrollTop > 80) {
            $header.addClass('scrolled');
        } else {
            $header.removeClass('scrolled');
        }

        // Hide/show on scroll direction
        if (scrollTop > 300) {
            if (scrollTop > lastScrollTop) {
                $header.addClass('nav-hidden');
            } else {
                $header.removeClass('nav-hidden');
            }
        }
        lastScrollTop = scrollTop;
    });

    // ==========================================
    // MOBILE MENU
    // ==========================================
    const $hamburger = $('#hamburger');
    const $navLinks = $('#nav-links');
    const $overlay = $('#mobile-overlay');

    $hamburger.on('click', function () {
        $(this).toggleClass('active');
        $navLinks.toggleClass('active');
        $overlay.toggleClass('active');
        $('body').toggleClass('menu-open');
    });

    $overlay.on('click', function () {
        $hamburger.removeClass('active');
        $navLinks.removeClass('active');
        $(this).removeClass('active');
        $('body').removeClass('menu-open');
        $('.nav-user').removeClass('open');
        $('.has-dropdown').removeClass('open');
    });

    // Mobile dropdown toggle
    $('.has-dropdown > a').on('click', function (e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            $(this).parent().toggleClass('open');
        }
    });
    // Mobile user dropdown toggle (button based)
    $(document).on('click', '.nav-user-btn', function (e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            $(this).closest('.nav-user').toggleClass('open');
        }
    });

    // ==========================================
    // DARK MODE TOGGLE
    // ==========================================
    const $themeToggle = $('#theme-toggle');
    const savedTheme = localStorage.getItem('pm_dairy_theme');

    if (savedTheme === 'dark') {
        $('body').addClass('dark-mode');
        $themeToggle.find('i').removeClass('fa-moon').addClass('fa-sun');
    }

    $themeToggle.on('click', function () {
        $('body').toggleClass('dark-mode');
        const isDark = $('body').hasClass('dark-mode');
        $(this).find('i').toggleClass('fa-moon fa-sun');
        localStorage.setItem('pm_dairy_theme', isDark ? 'dark' : 'light');
    });

    // ==========================================
    // BACK TO TOP
    // ==========================================
    const $backToTop = $('#back-to-top');

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 500) {
            $backToTop.addClass('visible');
        } else {
            $backToTop.removeClass('visible');
        }
    });

    $backToTop.on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 600);
    });

    // ==========================================
    // SMOOTH SCROLL
    // ==========================================
    $('a[href^="#"]').not('[data-close-modal]').on('click', function (e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({ scrollTop: target.offset().top - 80 }, 600);
        }
    });

    // ==========================================
    // SCROLL ANIMATIONS
    // ==========================================
    const animateOnScroll = function () {
        $('.animate-on-scroll').each(function () {
            const element = $(this);
            const elementTop = element.offset().top;
            const windowBottom = $(window).scrollTop() + $(window).height();

            if (elementTop < windowBottom - 50) {
                element.addClass('animated');
            }
        });
    };

    if (isSmallScreen || prefersReducedMotion) {
        // Avoid heavy scroll animations on small screens
        $('.animate-on-scroll').addClass('animated');
    } else if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.animate-on-scroll').forEach((el) => observer.observe(el));
    } else {
        $(window).on('scroll', animateOnScroll);
        animateOnScroll(); // Run on load
    }

    // ==========================================
    // TOAST NOTIFICATIONS
    // ==========================================
    window.showToast = function (message, type = 'success', duration = 3000) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const $toast = $(`
            <div class="toast toast-${type}">
                <i class="fas ${icons[type]}"></i>
                <span>${message}</span>
                <button class="toast-close">&times;</button>
            </div>
        `);

        $('#toast-container').append($toast);

        setTimeout(() => $toast.addClass('show'), 10);

        const autoClose = setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        }, duration);

        $toast.find('.toast-close').on('click', function () {
            clearTimeout(autoClose);
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        });
    };

    // ==========================================
    // CART FUNCTIONS (AJAX)
    // ==========================================
    window.PMCart = {
        add: function (productId, quantity = 1) {
            $.ajax({
                url: SITE_URL + '/api/cart/?action=add',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ product_id: productId, quantity: quantity }),
                success: function (res) {
                    if (res.success) {
                        showToast(res.message, 'success');
                        $('#cart-count').text(res.cart_count).addClass('cart-bump');
                        setTimeout(() => $('#cart-count').removeClass('cart-bump'), 300);
                    } else {
                        showToast(res.message, 'error');
                    }
                },
                error: function () {
                    showToast('Failed to add to cart', 'error');
                }
            });
        },

        update: function (cartItemId, quantity, callback) {
            $.ajax({
                url: SITE_URL + '/api/cart/?action=update',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ cart_item_id: cartItemId, quantity: quantity }),
                success: function (res) {
                    if (res.success) {
                        $('#cart-count').text(res.cart_count);
                        if (callback) callback(res);
                    }
                }
            });
        },

        remove: function (productId, callback) {
            $.ajax({
                url: SITE_URL + '/api/cart/?action=remove',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ product_id: productId }),
                success: function (res) {
                    if (res.success) {
                        showToast(res.message, 'info');
                        $('#cart-count').text(res.cart_count);
                        if (callback) callback(res);
                    }
                }
            });
        },

        load: function (callback) {
            $.ajax({
                url: SITE_URL + '/api/cart/',
                method: 'GET',
                success: function (res) {
                    if (callback) callback(res);
                }
            });
        }
    };

    // Add to cart button clicks
    $(document).on('click', '.btn-add-cart', function (e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const qty = $(this).closest('.product-detail-actions')?.find('.qty-input')?.val() || 1;
        PMCart.add(productId, parseInt(qty));
    });

    // ==========================================
    // AUTH FUNCTIONS
    // ==========================================
    window.PMAuth = {
        login: function (login, password, callback) {
            $.ajax({
                url: SITE_URL + '/api/auth/login.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ login: login, password: password }),
                success: function (res) {
                    if (res.success) {
                        showToast('Welcome back, ' + res.user.name + '!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    }
                    if (callback) callback(res);
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || { message: 'Login failed' };
                    showToast(res.message, 'error');
                    if (callback) callback(res);
                }
            });
        },

        register: function (data, callback) {
            $.ajax({
                url: SITE_URL + '/api/auth/register.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function (res) {
                    if (res.success) {
                        showToast('Registration successful!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    }
                    if (callback) callback(res);
                },
                error: function (xhr) {
                    const res = xhr.responseJSON || {};
                    if (res.errors) {
                        res.errors.forEach(err => showToast(err, 'error'));
                    } else {
                        showToast(res.message || 'Registration failed', 'error');
                    }
                    if (callback) callback(res);
                }
            });
        },

        logout: function () {
            $.post(SITE_URL + '/api/auth/logout.php', function () {
                showToast('Logged out', 'info');
                setTimeout(() => window.location.href = SITE_URL + '/', 1000);
            });
        }
    };

    // Logout button
    $(document).on('click', '#logout-btn', function (e) {
        e.preventDefault();
        PMAuth.logout();
    });

    // ==========================================
    // MODAL FUNCTIONS
    // ==========================================
    window.openModal = function (modalId) {
        $('#' + modalId).addClass('active');
        $('body').addClass('modal-open');
    };

    window.closeModal = function (modalId) {
        $('#' + modalId).removeClass('active');
        $('body').removeClass('modal-open');
    };

    $(document).on('click', '[data-close-modal]', function () {
        $(this).closest('.modal').removeClass('active');
        $('body').removeClass('modal-open');
    });

    $(document).on('click', '.modal-backdrop', function () {
        $(this).closest('.modal').removeClass('active');
        $('body').removeClass('modal-open');
    });

    // ==========================================
    // TESTIMONIALS SLIDER
    // ==========================================
    window.initTestimonialsSlider = function () {
        const $slider = $('.testimonials-slider');
        if (!$slider.length) return;

        const $slides = $slider.find('.testimonial-card');
        let currentSlide = 0;
        const totalSlides = $slides.length;

        function showSlide(index) {
            $slides.removeClass('active prev next');
            currentSlide = ((index % totalSlides) + totalSlides) % totalSlides;

            $slides.eq(currentSlide).addClass('active');
            $slides.eq((currentSlide - 1 + totalSlides) % totalSlides).addClass('prev');
            $slides.eq((currentSlide + 1) % totalSlides).addClass('next');

            // Update dots
            $slider.find('.slider-dot').removeClass('active');
            $slider.find('.slider-dot').eq(currentSlide).addClass('active');
        }

        // Init
        showSlide(0);

        // Navigation
        $slider.find('.slider-prev').on('click', () => showSlide(currentSlide - 1));
        $slider.find('.slider-next').on('click', () => showSlide(currentSlide + 1));
        $slider.find('.slider-dot').on('click', function () {
            showSlide($(this).index());
        });

        // Auto play
        let autoPlay = setInterval(() => showSlide(currentSlide + 1), 5000);
        $slider.on('mouseenter', () => clearInterval(autoPlay));
        $slider.on('mouseleave', () => {
            autoPlay = setInterval(() => showSlide(currentSlide + 1), 5000);
        });
    };

    // ==========================================
    // PRODUCT CAROUSEL (HOME PAGE)
    // ==========================================
    window.initProductCarousel = function () {
        const $carousel = $('.products-carousel');
        if (!$carousel.length) return;

        const $track = $carousel.find('.carousel-track');
        const $cards = $track.find('.product-card');
        let position = 0;

        function getVisibleCards() {
            if ($(window).width() < 576) return 1;
            if ($(window).width() < 768) return 2;
            if ($(window).width() < 1024) return 3;
            return 4;
        }

        function updateCarousel() {
            const cardWidth = $cards.first().outerWidth(true);
            const maxPos = Math.max(0, $cards.length - getVisibleCards());
            position = Math.min(position, maxPos);
            $track.css('transform', `translateX(-${position * cardWidth}px)`);
        }

        $carousel.find('.carousel-prev').on('click', function () {
            if (position > 0) {
                position--;
                updateCarousel();
            }
        });

        $carousel.find('.carousel-next').on('click', function () {
            const maxPos = $cards.length - getVisibleCards();
            if (position < maxPos) {
                position++;
                updateCarousel();
            }
        });

        $(window).on('resize', updateCarousel);
    };

    // ==========================================
    // IMAGE LIGHTBOX
    // ==========================================
    window.initLightbox = function () {
        $(document).on('click', '[data-lightbox]', function (e) {
            e.preventDefault();
            const src = $(this).attr('href') || $(this).data('lightbox');
            const title = $(this).data('title') || '';

            const $lightbox = $(`
                <div class="lightbox-overlay">
                    <div class="lightbox-content">
                        <button class="lightbox-close">&times;</button>
                        <img src="${src}" alt="${title}">
                        ${title ? `<p class="lightbox-caption">${title}</p>` : ''}
                    </div>
                </div>
            `);

            $('body').append($lightbox);
            setTimeout(() => $lightbox.addClass('active'), 10);

            function closeLightbox() {
                $lightbox.removeClass('active');
                setTimeout(() => $lightbox.remove(), 300);
            }

            $lightbox.on('click', function (ev) {
                if (ev.target === this || $(ev.target).hasClass('lightbox-close')) {
                    closeLightbox();
                }
            });

            $lightbox.find('.lightbox-close').on('click', closeLightbox);
        });
    };

    initLightbox();

    // ==========================================
    // COUNTER ANIMATION
    // ==========================================
    window.initCounters = function () {
        $('.stat-number[data-count]').each(function () {
            const $el = $(this);
            if ($el.hasClass('counted')) return;

            const target = $el.data('count');
            const elementTop = $el.offset().top;
            const windowBottom = $(window).scrollTop() + $(window).height();

            if (elementTop < windowBottom) {
                $el.addClass('counted');
                $({ count: 0 }).animate({ count: target }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function () {
                        $el.text(Math.floor(this.count));
                    },
                    complete: function () {
                        $el.text(target + ($el.data('suffix') || ''));
                    }
                });
            }
        });
    };

    $(window).on('scroll', initCounters);
    initCounters();

    // ==========================================
    // FORM HELPERS
    // ==========================================
    // Quantity selector
    $(document).on('click', '.qty-btn', function () {
        const $input = $(this).siblings('.qty-input');
        let val = parseInt($input.val()) || 1;

        if ($(this).hasClass('qty-minus')) {
            val = Math.max(1, val - 1);
        } else {
            val = Math.min(99, val + 1);
        }

        $input.val(val).trigger('change');
    });

    // Form validation visual feedback
    $(document).on('blur', '.form-input[required]', function () {
        if (!$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });

    $(document).on('input', '.form-input.is-invalid', function () {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
        }
    });

    // ==========================================
    // SEARCH FUNCTIONALITY
    // ==========================================
    let searchTimer;
    $(document).on('input', '#global-search', function () {
        clearTimeout(searchTimer);
        const query = $(this).val().trim();
        const $results = $('#search-results');

        if (query.length < 2) {
            $results.html('').hide();
            return;
        }

        searchTimer = setTimeout(function () {
            $.get(SITE_URL + '/api/products/', { search: query, limit: 5 }, function (res) {
                if (res.success && res.products.length) {
                    let html = '<ul class="search-dropdown">';
                    res.products.forEach(function (p) {
                        const img = p.images.length ? SITE_URL + p.images[0] : SITE_URL + '/assets/images/placeholder.jpg';
                        html += `<li><a href="${SITE_URL}/pages/product-detail.php?slug=${p.slug}">
                            <img src="${img}" alt="${p.name}"><span>${p.name}</span>
                            <span class="search-price">₹${parseFloat(p.price).toFixed(2)}</span></a></li>`;
                    });
                    html += '</ul>';
                    $results.html(html).show();
                } else {
                    $results.html('<p class="search-no-results">No products found</p>').show();
                }
            });
        }, 300);
    });

    // Close search on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $('#search-results').hide();
        }
    });

    // ==========================================
    // LAZY LOADING IMAGES
    // ==========================================
    if ('IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imgObserver.unobserve(img);
                }
            });
        }, { rootMargin: '50px' });

        document.querySelectorAll('img[data-src]').forEach(img => imgObserver.observe(img));
    }

    // ==========================================
    // TABS
    // ==========================================
    $(document).on('click', '.tab-btn', function () {
        const target = $(this).data('tab');
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $(this).closest('.tabs-wrapper').find('.tab-pane').removeClass('active');
        $(this).closest('.tabs-wrapper').find('#' + target).addClass('active');
    });

    // ==========================================
    // INITIALIZE COMPONENTS
    // ==========================================
    initTestimonialsSlider();
    initProductCarousel();
});
