<?php
/**
 * API: Blogs
 * GET /api/blogs/ - List blogs
 * GET /api/blogs/?slug=xxx - Blog detail
 * POST /api/blogs/ - Create blog (admin)
 * POST /api/blogs/?action=update&id=1 - Update blog (admin)
 * POST /api/blogs/?action=delete&id=1 - Delete blog (admin)
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $slug = sanitize($_GET['slug'] ?? '');

    // Single blog detail
    if (!empty($slug)) {
        $blog = $db->fetchOne(
            "SELECT * FROM blogs WHERE slug = ? AND is_published = 1",
            [$slug], 's'
        );
        if (!$blog) {
            jsonResponse(['success' => false, 'message' => 'Blog not found'], 404);
        }

        // Increment views
        $db->update("UPDATE blogs SET views = views + 1 WHERE id = ?", [$blog['id']], 'i');

        // Related posts
        $related = $db->fetchAll(
            "SELECT id, title, slug, excerpt, featured_image, category, published_at FROM blogs
             WHERE category = ? AND id != ? AND is_published = 1 ORDER BY published_at DESC LIMIT 3",
            [$blog['category'], $blog['id']], 'si'
        );

        jsonResponse(['success' => true, 'blog' => $blog, 'related' => $related]);
    }

    // Blog listing
    $category = sanitize($_GET['category'] ?? '');
    $search = sanitize($_GET['search'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));

    $where = ["is_published = 1"];
    $params = [];
    $types = '';

    if (!empty($category)) {
        $where[] = "category = ?";
        $params[] = $category;
        $types .= 's';
    }

    if (!empty($search)) {
        $where[] = "(title LIKE ? OR content LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $types .= 'ss';
    }

    $whereClause = implode(' AND ', $where);
    $countRow = $db->fetchOne("SELECT COUNT(*) as total FROM blogs WHERE $whereClause", $params, $types);
    $pagination = getPagination($countRow['total'], $page);

    $blogs = $db->fetchAll(
        "SELECT id, title, slug, excerpt, featured_image, category, author, views, published_at
         FROM blogs WHERE $whereClause ORDER BY published_at DESC
         LIMIT " . ITEMS_PER_PAGE . " OFFSET {$pagination['offset']}",
        $params, $types
    );

    // Get category counts
    $categories = $db->fetchAll(
        "SELECT category, COUNT(*) as count FROM blogs WHERE is_published = 1 GROUP BY category"
    );

    jsonResponse(['success' => true, 'blogs' => $blogs, 'categories' => $categories, 'pagination' => $pagination]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = Auth::requireAdmin();
    $action = sanitize($_GET['action'] ?? '');

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        $blog = $db->fetchOne("SELECT featured_image FROM blogs WHERE id = ?", [$id], 'i');
        if ($blog && $blog['featured_image']) deleteImage($blog['featured_image']);
        $db->update("DELETE FROM blogs WHERE id = ?", [$id], 'i');
        jsonResponse(['success' => true, 'message' => 'Blog deleted']);
    }

    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $category = sanitize($_POST['category'] ?? 'farm-updates');
    $isPublished = intval($_POST['is_published'] ?? 0);
    $metaTitle = sanitize($_POST['meta_title'] ?? '');
    $metaDesc = sanitize($_POST['meta_description'] ?? '');

    if (empty($title) || empty($content)) {
        jsonResponse(['success' => false, 'message' => 'Title and content are required'], 422);
    }

    // Handle image upload
    $imagePath = null;
    if (!empty($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['featured_image'], 'blog');
        if ($result['success']) $imagePath = $result['path'];
    }

    if ($action === 'update') {
        $id = intval($_GET['id'] ?? 0);
        $fields = "title = ?, content = ?, excerpt = ?, category = ?, is_published = ?, meta_title = ?, meta_description = ?";
        $params = [$title, $content, $excerpt, $category, $isPublished, $metaTitle, $metaDesc];
        $types = 'ssssiss';

        if ($imagePath) {
            $fields .= ", featured_image = ?";
            $params[] = $imagePath;
            $types .= 's';
        }

        if ($isPublished) {
            $fields .= ", published_at = COALESCE(published_at, NOW())";
        }

        $params[] = $id;
        $types .= 'i';

        $db->update("UPDATE blogs SET $fields WHERE id = ?", $params, $types);
        jsonResponse(['success' => true, 'message' => 'Blog updated']);
    }

    // Create new blog
    $slug = generateSlug($title);
    $existing = $db->fetchOne("SELECT id FROM blogs WHERE slug = ?", [$slug], 's');
    if ($existing) $slug .= '-' . time();

    $publishedAt = $isPublished ? date('Y-m-d H:i:s') : null;

    $blogId = $db->insert(
        "INSERT INTO blogs (title, slug, content, excerpt, featured_image, category, is_published, published_at, meta_title, meta_description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$title, $slug, $content, $excerpt, $imagePath, $category, $isPublished, $publishedAt, $metaTitle, $metaDesc],
        'ssssssssss'
    );

    jsonResponse(['success' => true, 'message' => 'Blog created', 'blog_id' => $blogId], 201);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
