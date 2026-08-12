<?php
/**
 * Farm Visit / Tour Booking Page
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Book a Farm Visit';
$pageDescription = 'Book a tour of PM Dairy Farm. Experience farm life, meet our cattle, and see how fresh dairy products are made.';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero page-hero-sm">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <div class="page-hero-content">
            <nav class="breadcrumb">
                <a href="<?= SITE_URL ?>/">Home</a><span>/</span><span class="current">Farm Visit</span>
            </nav>
            <h1>Book a Farm Visit</h1>
            <p>Experience the joy of farm life with your family</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="farm-visit-grid">
            <!-- Booking Form -->
            <div class="visit-form-wrapper animate-on-scroll slide-left">
                <h2><i class="fas fa-calendar-check"></i> Book Your Visit</h2>
                <p>Fill out the form below to request a farm visit. We'll confirm your booking via phone call within 24 hours.</p>

                <form id="visit-form" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="tel" name="phone" class="form-input" placeholder="10-digit mobile number" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" placeholder="your@email.com">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Preferred Date <span class="required">*</span></label>
                            <input type="date" name="preferred_date" class="form-input" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Number of Visitors <span class="required">*</span></label>
                            <select name="visitors" class="form-input form-select" required>
                                <?php for ($i = 1; $i <= 20; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'person' : 'people' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Special Requests / Message</label>
                        <textarea name="message" class="form-input form-textarea" rows="3" placeholder="Any special requests or questions..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" id="visit-submit">
                        <i class="fas fa-calendar-plus"></i> Book Visit
                    </button>
                </form>
            </div>

            <!-- Visit Info -->
            <div class="visit-info-wrapper animate-on-scroll slide-right">
                <!-- Timings -->
                <div class="visit-info-card">
                    <h3><i class="fas fa-clock"></i> Visit Timings</h3>
                    <ul class="visit-timings">
                        <li><strong>Morning Slot:</strong> 7:00 AM - 10:00 AM</li>
                        <li><strong>Evening Slot:</strong> 4:00 PM - 6:30 PM</li>
                        <li><strong>Available Days:</strong> All 7 days</li>
                        <li><strong>Duration:</strong> Approx. 1.5 - 2 hours</li>
                        <li><strong>Entry Fee:</strong> Free (for families)</li>
                    </ul>
                </div>

                <!-- What to Expect -->
                <div class="visit-info-card">
                    <h3><i class="fas fa-star"></i> What to Expect</h3>
                    <div class="expect-grid">
                        <div class="expect-item">
                            <div class="expect-icon">🐄</div>
                            <span>Meet Our Cattle</span>
                        </div>
                        <div class="expect-item">
                            <div class="expect-icon">🥛</div>
                            <span>Watch Milking</span>
                        </div>
                        <div class="expect-item">
                            <div class="expect-icon">🧈</div>
                            <span>See Ghee Making</span>
                        </div>
                        <div class="expect-item">
                            <div class="expect-icon">🌿</div>
                            <span>Farm Walk</span>
                        </div>
                        <div class="expect-item">
                            <div class="expect-icon">📸</div>
                            <span>Photo Opportunities</span>
                        </div>
                        <div class="expect-item">
                            <div class="expect-icon">🥤</div>
                            <span>Fresh Milk Tasting</span>
                        </div>
                    </div>
                </div>

                <!-- Rules -->
                <div class="visit-info-card">
                    <h3><i class="fas fa-info-circle"></i> Visit Guidelines</h3>
                    <ul class="visit-rules">
                        <li>Please arrive on time for your scheduled slot</li>
                        <li>Wear comfortable shoes suitable for walking on farm grounds</li>
                        <li>Children must be accompanied by an adult at all times</li>
                        <li>Do not feed the animals without staff permission</li>
                        <li>Photography is allowed and encouraged!</li>
                        <li>Fresh products are available for purchase at the farm shop</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="map-section mt-5 animate-on-scroll fade-up">
            <h3 class="text-center mb-3">How to Reach Us</h3>
            <div class="map-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3451.5!2d76.6!3d29.9!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjnCsDU0JzAwLjAiTiA3NsKwMzYnMDAuMCJF!5e0!3m2!1sen!2sin!4v1234567890"
                        width="100%" height="350" style="border:0;border-radius:12px" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#visit-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#visit-submit');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Booking...');

        $.ajax({
            url: SITE_URL + '/api/farm-visits/',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                name: $('[name="name"]', this).val(),
                phone: $('[name="phone"]', this).val(),
                email: $('[name="email"]', this).val(),
                preferred_date: $('[name="preferred_date"]', this).val(),
                visitors: $('[name="visitors"]', this).val(),
                message: $('[name="message"]', this).val()
            }),
            success: function(res) {
                if (res.success) {
                    showToast(res.message, 'success', 5000);
                    $('#visit-form')[0].reset();
                } else {
                    if (res.errors) res.errors.forEach(e => showToast(e, 'error'));
                    else showToast(res.message, 'error');
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON || {};
                showToast(res.message || 'Booking failed. Please try again.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-calendar-plus"></i> Book Visit');
            }
        });
    });
});
</script>
