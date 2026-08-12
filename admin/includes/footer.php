        </div><!-- End admin-content -->
    </div><!-- End admin-main -->

    <div id="toast-container" class="toast-container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        const SITE_URL = '<?= SITE_URL ?>';
        const IS_LOGGED_IN = true;
        const USER_ROLE = 'admin';
    </script>
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
    <script>
        // Admin sidebar toggle
        $('#sidebar-toggle, #mobile-sidebar-toggle').on('click', function() {
            $('#admin-sidebar').toggleClass('collapsed');
            $('.admin-main').toggleClass('expanded');
        });

        // Admin logout
        window.PMAuth = window.PMAuth || {};
        PMAuth.logout = function() {
            $.post(SITE_URL + '/api/auth/logout.php', function() {
                window.location.href = SITE_URL + '/pages/login.php';
            });
        };
    </script>

    <?php if (isset($adminExtraJS)): ?>
        <?php foreach ($adminExtraJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($adminPageScript)): ?>
        <script><?= $adminPageScript ?></script>
    <?php endif; ?>
</body>
</html>
