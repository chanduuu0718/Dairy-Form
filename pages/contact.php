<?php
/**
 * Contact Us Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with PM Dairy Farm. Call, WhatsApp, or visit us. We love hearing from our customers!';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Contact Us</span>
            </nav>
            <h1>Contact Us</h1>
            <p>We'd love to hear from you. Reach out anytime!</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-wrapper animate-on-scroll slide-left">
                <h2>Send Us a Message</h2>
                <p>Fill out the form below and we'll get back to you within 24 hours.</p>

                <form id="contact-form" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Your Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" placeholder="10-digit mobile number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-input" placeholder="your@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject <span class="required">*</span></label>
                        <select name="subject" class="form-input form-select" required>
                            <option value="">Select a subject</option>
                            <option value="Product Inquiry">Product Inquiry</option>
                            <option value="Order Issue">Order Issue</option>
                            <option value="Delivery Query">Delivery Query</option>
                            <option value="Subscription">Subscription</option>
                            <option value="Farm Visit">Farm Visit</option>
                            <option value="Feedback">Feedback</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message <span class="required">*</span></label>
                        <textarea name="message" class="form-input form-textarea" rows="5" placeholder="Write your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" id="contact-submit">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info-wrapper animate-on-scroll slide-right">
                <div class="contact-info-card">
                    <h3>Get in Touch</h3>
                    <div class="contact-info-list">
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <strong>Farm Address</strong>
                                <p><?= SITE_ADDRESS ?></p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <strong>Phone</strong>
                                <p><a href="tel:<?= SITE_PHONE ?>"><?= SITE_PHONE ?></a></p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fab fa-whatsapp"></i></div>
                            <div>
                                <strong>WhatsApp</strong>
                                <p><a href="https://wa.me/<?= SITE_WHATSAPP ?>">Chat on WhatsApp</a></p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <strong>Email</strong>
                                <p><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <strong>Working Hours</strong>
                                <p>Mon - Sun: 6:00 AM - 8:00 PM</p>
                                <p>Delivery: 6:00 AM - 10:00 AM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="contact-social-card">
                    <h4>Follow Us</h4>
                    <div class="social-links-large">
                        <a href="javascript:void(0);" class="social-link instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/@PurvanchalDairyfarm" class="social-link youtube"><i class="fab fa-youtube"></i></a>
                        <a href="https://wa.me/<?= SITE_WHATSAPP ?>" class="social-link whatsapp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Map -->
        <div class="map-section mt-5 animate-on-scroll fade-up">
            <h3 class="text-center mb-3">Find Us on Map</h3>
            <div class="map-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d20275.959984652578!2d83.05909579381293!3d26.05588797609847!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3991b00716aea437%3A0x7db5173ef4ca9a05!2sBegpur%20Aima%2C%20Uttar%20Pradesh%20276206!5e0!3m2!1sen!2sin!4v1771618807106!5m2!1sen!2sin"
                        width="100%" height="400" style="border:0; border-radius:12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#contact-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#contact-submit');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: SITE_URL + '/api/contact/',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                name: $('[name="name"]').val(),
                phone: $('[name="phone"]').val(),
                email: $('[name="email"]').val(),
                subject: $('[name="subject"]').val(),
                message: $('[name="message"]').val()
            }),
            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    $('#contact-form')[0].reset();
                } else {
                    if (res.errors) res.errors.forEach(e => showToast(e, 'error'));
                    else showToast(res.message, 'error');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                showToast(res.message || 'Failed to send message', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Message');
            }
        });
    });
});
</script>
