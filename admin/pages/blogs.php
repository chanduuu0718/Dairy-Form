<?php
/**
 * Admin: Blog Posts Management
 */
$adminTitle = 'Blog Posts';
require_once __DIR__ . '/../includes/header.php';

// Fetch all blogs (including drafts)
$blogs = $db->fetchAll("SELECT * FROM blogs ORDER BY created_at DESC");

$blogCategories = [
    'health-tips' => 'Health Tips',
    'farm-updates' => 'Farm Updates',
    'dairy-knowledge' => 'Dairy Knowledge',
    'recipes' => 'Recipes'
];
?>

<div class="admin-page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
    <h1 class="admin-page-title" style="margin:0;">Blog Posts</h1>
    <button class="btn btn-primary" id="btn-add-blog">
        <i class="fas fa-plus"></i> Add Blog Post
    </button>
</div>

<!-- Blogs Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Posts (<?= count($blogs) ?>)</h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blogs)): ?>
                    <tr><td colspan="7" class="text-center">No blog posts found</td></tr>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                        <tr id="blog-row-<?= $blog['id'] ?>">
                            <td>
                                <?php if (!empty($blog['featured_image'])): ?>
                                    <img src="<?= SITE_URL . $blog['featured_image'] ?>" alt=""
                                         style="width:60px; height:40px; object-fit:cover; border-radius:6px;">
                                <?php else: ?>
                                    <div style="width:60px; height:40px; background:#f0f0f0; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars(truncateText($blog['title'], 50)) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= getBlogCategoryName($blog['category']) ?></span>
                            </td>
                            <td>
                                <?php if ($blog['is_published']): ?>
                                    <span class="badge badge-success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><i class="fas fa-eye text-muted"></i> <?= number_format($blog['views'] ?? 0) ?></td>
                            <td>
                                <?= $blog['published_at'] ? date('d M Y', strtotime($blog['published_at'])) : date('d M Y', strtotime($blog['created_at'])) ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <button class="btn btn-sm btn-outline-primary btn-edit-blog"
                                            data-id="<?= $blog['id'] ?>"
                                            data-title="<?= htmlspecialchars($blog['title']) ?>"
                                            data-content="<?= htmlspecialchars($blog['content'] ?? '') ?>"
                                            data-excerpt="<?= htmlspecialchars($blog['excerpt'] ?? '') ?>"
                                            data-category="<?= $blog['category'] ?>"
                                            data-published="<?= $blog['is_published'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-blog" data-id="<?= $blog['id'] ?>" data-title="<?= htmlspecialchars($blog['title']) ?>">
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

<!-- Add/Edit Blog Form -->
<div class="admin-card mt-4" id="blog-form-card" style="display:none;">
    <div class="admin-card-header">
        <h2 id="blog-form-title">Add New Blog Post</h2>
        <button class="btn btn-sm btn-outline-secondary" id="btn-cancel-blog">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
    <form id="blog-form" enctype="multipart/form-data" style="padding:20px;">
        <input type="hidden" id="blog-id" value="">

        <div class="form-group">
            <label class="form-label">Title *</label>
            <input type="text" name="title" id="b-title" class="form-input" required>
        </div>

        <div class="form-group">
            <label class="form-label">Content *</label>
            <textarea name="content" id="b-content" class="form-input" rows="12" required></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Excerpt</label>
            <textarea name="excerpt" id="b-excerpt" class="form-input" rows="3" maxlength="300" placeholder="Short summary of the blog post..."></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" id="b-category" class="form-input form-select">
                    <?php foreach ($blogCategories as $slug => $name): ?>
                        <option value="<?= $slug ?>"><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:15px; padding-top:25px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="is_published" id="b-published" value="1">
                    <span>Publish immediately</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Featured Image</label>
            <input type="file" name="featured_image" id="b-image" class="form-input" accept="image/*">
            <small class="text-muted">Recommended: 1200x630px. Max 5MB.</small>
        </div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary" id="btn-save-blog">
                <i class="fas fa-save"></i> Save Post
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-cancel-blog-2">Cancel</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const $formCard = $('#blog-form-card');
    const $form = $('#blog-form');

    // Show add form
    $('#btn-add-blog').on('click', function() {
        $form[0].reset();
        $('#blog-id').val('');
        $('#blog-form-title').text('Add New Blog Post');
        $formCard.slideDown();
        $('html, body').animate({ scrollTop: $formCard.offset().top - 80 }, 300);
    });

    // Cancel
    $('#btn-cancel-blog, #btn-cancel-blog-2').on('click', function() {
        $formCard.slideUp();
        $form[0].reset();
        $('#blog-id').val('');
    });

    // Edit blog
    $('.btn-edit-blog').on('click', function() {
        const $btn = $(this);
        $('#blog-id').val($btn.data('id'));
        $('#b-title').val($btn.data('title'));
        $('#b-content').val($btn.data('content'));
        $('#b-excerpt').val($btn.data('excerpt'));
        $('#b-category').val($btn.data('category'));
        $('#b-published').prop('checked', $btn.data('published') == 1);
        $('#blog-form-title').text('Edit Blog Post');
        $formCard.slideDown();
        $('html, body').animate({ scrollTop: $formCard.offset().top - 80 }, 300);
    });

    // Submit blog form
    $form.on('submit', function(e) {
        e.preventDefault();

        const blogId = $('#blog-id').val();
        const formData = new FormData(this);

        if (!$('#b-published').is(':checked')) {
            formData.set('is_published', '0');
        }

        let url;
        if (blogId) {
            url = SITE_URL + '/api/blogs/?action=update&id=' + blogId;
        } else {
            url = SITE_URL + '/api/blogs/';
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    showToast(blogId ? 'Blog post updated' : 'Blog post created', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.message || 'Failed to save blog post', 'error');
                }
            },
            error: function() {
                showToast('Failed to save blog post', 'error');
            }
        });
    });

    // Delete blog
    $('.btn-delete-blog').on('click', function() {
        const blogId = $(this).data('id');
        const blogTitle = $(this).data('title');

        if (!confirm('Are you sure you want to delete "' + blogTitle + '"? This cannot be undone.')) {
            return;
        }

        $.ajax({
            url: SITE_URL + '/api/blogs/?action=delete&id=' + blogId,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            success: function(res) {
                if (res.success) {
                    showToast('Blog post deleted', 'success');
                    $('#blog-row-' + blogId).fadeOut(300, function() { $(this).remove(); });
                } else {
                    showToast(res.message || 'Failed to delete', 'error');
                }
            },
            error: function() {
                showToast('Failed to delete blog post', 'error');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
