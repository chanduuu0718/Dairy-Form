<?php
/**
 * Admin: Users (Customers) Management
 */
$adminTitle = 'Customers';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="admin-page-title">Customer Management</h1>

<!-- Search -->
<div class="admin-card mb-4">
    <div style="display:flex; gap:15px; align-items:flex-end; padding:20px; flex-wrap:wrap;">
        <div class="form-group" style="flex:1; min-width:250px;">
            <label class="form-label">Search Customers</label>
            <input type="text" id="user-search" class="form-input" placeholder="Search by name, phone, or email...">
        </div>
        <div class="form-group">
            <button class="btn btn-primary" id="btn-search-users">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2>All Customers <span id="users-total-count"></span></h2>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Verified</th>
                    <th>Joined Date</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                <tr><td colspan="6" class="text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="users-pagination" class="admin-pagination" style="display:flex; justify-content:center; gap:5px; padding:20px;"></div>
</div>

<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentSearch = '';

    function loadUsers(page, search) {
        currentPage = page;
        currentSearch = search;

        const params = { page: page };
        if (search) params.search = search;

        $.ajax({
            url: SITE_URL + '/api/users/',
            method: 'GET',
            data: params,
            success: function(res) {
                if (res.success) {
                    renderUsers(res.users, res.pagination);
                } else {
                    showToast(res.message || 'Failed to load customers', 'error');
                }
            },
            error: function() {
                showToast('Failed to load customers', 'error');
                $('#users-table-body').html('<tr><td colspan="6" class="text-center">Failed to load data</td></tr>');
            }
        });
    }

    function renderUsers(users, pagination) {
        const $tbody = $('#users-table-body');
        $tbody.empty();

        $('#users-total-count').text('(' + pagination.total + ')');

        if (users.length === 0) {
            $tbody.html('<tr><td colspan="6" class="text-center">No customers found</td></tr>');
            $('#users-pagination').empty();
            return;
        }

        const offset = pagination.offset || 0;
        users.forEach(function(user, index) {
            const verified = user.is_verified == 1
                ? '<span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>'
                : '<span class="badge badge-warning">Not verified</span>';

            const joinedDate = new Date(user.created_at);
            const dateStr = joinedDate.toLocaleDateString('en-IN', {
                day: '2-digit', month: 'short', year: 'numeric'
            });

            $tbody.append(`
                <tr>
                    <td>${offset + index + 1}</td>
                    <td><strong>${escHtml(user.name)}</strong></td>
                    <td>${escHtml(user.phone)}</td>
                    <td>${user.email ? escHtml(user.email) : '<span class="text-muted">-</span>'}</td>
                    <td>${verified}</td>
                    <td>${dateStr}</td>
                </tr>
            `);
        });

        // Render pagination
        renderPagination(pagination);
    }

    function renderPagination(pagination) {
        const $pag = $('#users-pagination');
        $pag.empty();

        if (pagination.total_pages <= 1) return;

        if (pagination.has_prev) {
            $pag.append(`<button class="btn btn-sm btn-outline-primary page-btn" data-page="${pagination.current_page - 1}">&laquo; Prev</button>`);
        }

        const start = Math.max(1, pagination.current_page - 2);
        const end = Math.min(pagination.total_pages, pagination.current_page + 2);
        for (let i = start; i <= end; i++) {
            const active = i === pagination.current_page ? 'btn-primary' : 'btn-outline-primary';
            $pag.append(`<button class="btn btn-sm ${active} page-btn" data-page="${i}">${i}</button>`);
        }

        if (pagination.has_next) {
            $pag.append(`<button class="btn btn-sm btn-outline-primary page-btn" data-page="${pagination.current_page + 1}">Next &raquo;</button>`);
        }
    }

    function escHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Event handlers
    $('#btn-search-users').on('click', function() {
        loadUsers(1, $('#user-search').val().trim());
    });

    $('#user-search').on('keypress', function(e) {
        if (e.which === 13) {
            loadUsers(1, $(this).val().trim());
        }
    });

    $(document).on('click', '.page-btn', function() {
        loadUsers($(this).data('page'), currentSearch);
    });

    // Initial load
    loadUsers(1, '');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
