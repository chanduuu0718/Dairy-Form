<?php
/**
 * Admin: Testimonials Management
 */
$adminTitle = 'Testimonials';
require_once __DIR__ . '/../includes/header.php';

// Fetch all testimonials
$testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC");
?>

<div class="admin-page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
    <h1 class="admin-page-title" style="margin:0;">Testimonials</h1>
    <button class="btn btn-primary" id="btn-add-testimonial">
        <i class="fas fa-plus"></i> Add Testimonial
    </button>
</div>

<!-- Testimonials Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Testimonials (<?= count($testimonials) ?>)</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Approved</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonials)): ?>
                    <tr><td colspan="7" class="text-center">No testimonials found</td></tr>
                <?php else: ?>
                    <?php foreach ($testimonials as $test): ?>
                        <tr id="testimonial-row-<?= $test['id'] ?>">
                            <td>
                                <?php if (!empty($test['customer_photo'])): ?>
                                    <img src="<?= SITE_URL . $test['customer_photo'] ?>" alt=""
                                         style="width:45px; height:45px; object-fit:cover; border-radius:50%;">
                                <?php else: ?>
                                    <div style="width:45px; height:45px; background:#e8f5e9; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:600; color:#27ae60;">
                                        <?= strtoupper(substr($test['customer_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($test['customer_name']) ?></strong></td>
                            <td><?= htmlspecialchars($test['customer_location'] ?? '-') ?></td>
                            <td>
                                <div style="color:#f39c12; white-space:nowrap;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $test['rating'] ? '' : '-half-alt' ?>" style="<?= $i > $test['rating'] ? 'opacity:0.3;' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <span title="<?= htmlspecialchars($test['review_text']) ?>">
                                    <?= htmlspecialchars(truncateText($test['review_text'], 80)) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($test['is_approved']): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px; flex-wrap:nowrap;">
                                    <?php if (!$test['is_approved']): ?>
                                        <button class="btn btn-sm btn-outline-success btn-approve-testimonial" data-id="<?= $test['id'] ?>" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-warning btn-reject-testimonial" data-id="<?= $test['id'] ?>" title="Reject">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-testimonial" data-id="<?= $test['id'] ?>" data-name="<?= htmlspecialchars($test['customer_name']) ?>" title="Delete">
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

<!-- Add Testimonial Form -->
<div class="admin-card mt-4" id="testimonial-form-card" style="display:none;">
    <div class="admin-card-header">
        <h2>Add New Testimonial</h2>
        <button class="btn btn-sm btn-outline-secondary" id="btn-cancel-testimonial">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
    <form id="testimonial-form" enctype="multipart/form-data" style="padding:20px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
                <label class="form-label">Customer Name *</label>
                <input type="text" name="customer_name" id="t-name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="customer_location" id="t-location" class="form-input" placeholder="e.g. Kurukshetra, Haryana">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Rating *</label>
            <div class="star-rating-input" id="star-rating-input">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star star-btn" data-value="<?= $i ?>" style="cursor:pointer; font-size:24px; color:#ddd; transition:color 0.2s;"></i>
                <?php endfor; ?>
                <input type="hidden" name="rating" id="t-rating" value="5">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Review Text *</label>
            <textarea name="review_text" id="t-review" class="form-input" rows="4" required placeholder="Write the customer review..."></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Customer Photo</label>
            <input type="file" name="customer_photo" id="t-photo" class="form-input" accept="image/*">
            <small class="text-muted">Optional. Square images work best. Max 5MB.</small>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Testimonial
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-cancel-testimonial-2">Cancel</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const $formCard = $('#testimonial-form-card');
    const $form = $('#testimonial-form');

    // Star rating input
    let selectedRating = 5;
    function updateStars(rating) {
        selectedRating = rating;
        $('#t-rating').val(rating);
        $('.star-btn').each(function() {
            const val = $(this).data('value');
            $(this).css('color', val <= rating ? '#f39c12' : '#ddd');
        });
    }
    updateStars(5);

    $('.star-btn').on('click', function() {
        updateStars($(this).data('value'));
    }).on('mouseenter', function() {
        const hoverVal = $(this).data('value');
        $('.star-btn').each(function() {
            $(this).css('color', $(this).data('value') <= hoverVal ? '#f39c12' : '#ddd');
        });
    });

    $('#star-rating-input').on('mouseleave', function() {
        updateStars(selectedRating);
    });

    // Show add form
    $('#btn-add-testimonial').on('click', function() {
        $form[0].reset();
        updateStars(5);
        $formCard.slideDown();
        $('html, body').animate({ scrollTop: $formCard.offset().top - 80 }, 300);
    });

    // Cancel
    $('#btn-cancel-testimonial, #btn-cancel-testimonial-2').on('click', function() {
        $formCard.slideUp();
        $form[0].reset();
    });

    // Submit testimonial form
    $form.on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: SITE_URL + '/api/testimonials/',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    showToast('Testimonial added successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.message || 'Failed to add testimonial', 'error');
                }
            },
            error: function() {
                showToast('Failed to add testimonial', 'error');
            }
        });
    });

    // Approve testimonial
    $(document).on('click', '.btn-approve-testimonial', function() {
        const id = $(this).data('id');
        $.ajax({
            url: SITE_URL + '/api/testimonials/?action=approve',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(res) {
                if (res.success) {
                    showToast('Testimonial approved', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(res.message || 'Failed to approve', 'error');
                }
            },
            error: function() {
                showToast('Failed to approve testimonial', 'error');
            }
        });
    });

    // Reject testimonial
    $(document).on('click', '.btn-reject-testimonial', function() {
        const id = $(this).data('id');
        $.ajax({
            url: SITE_URL + '/api/testimonials/?action=reject',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(res) {
                if (res.success) {
                    showToast('Testimonial rejected', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(res.message || 'Failed to reject', 'error');
                }
            },
            error: function() {
                showToast('Failed to reject testimonial', 'error');
            }
        });
    });

    // Delete testimonial
    $(document).on('click', '.btn-delete-testimonial', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        if (!confirm('Are you sure you want to delete the testimonial from "' + name + '"?')) return;

        $.ajax({
            url: SITE_URL + '/api/testimonials/?action=delete',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: id }),
            success: function(res) {
                if (res.success) {
                    showToast('Testimonial deleted', 'success');
                    $('#testimonial-row-' + id).fadeOut(300, function() { $(this).remove(); });
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            },
            error: function() {
                showToast('Failed to delete testimonial', 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
