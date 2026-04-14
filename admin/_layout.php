<?php

declare(strict_types=1);

function admin_header(string $title, string $activePage = 'dashboard'): void
{
    $user = admin_user();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title . ' | Admin | ' . site_name()) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?= e(url('assets/css/site.css')) ?>" rel="stylesheet">
    </head>
    <body class="admin-shell">
    <div class="container-fluid">
        <div class="row">
            <aside class="col-lg-2 px-4 py-4 admin-sidebar">
                <a class="d-inline-block fs-4 fw-bold mb-4 text-white" href="<?= e(url('admin/index.php')) ?>"><?= e(site_name()) ?></a>
                <div class="small text-white-50">Signed in as <?= e($user['name'] ?? 'Admin') ?></div>
                <div class="small text-white-50 mb-4"><?= e(admin_role_label($user['role'] ?? 'admin')) ?></div>
                <nav class="d-flex flex-column gap-3">
                    <a class="<?= $activePage === 'dashboard' ? 'active fw-semibold' : '' ?>" href="<?= e(url('admin/index.php')) ?>">Dashboard</a>
                    <a class="<?= $activePage === 'properties' ? 'active fw-semibold' : '' ?>" href="<?= e(url('admin/properties.php')) ?>">Properties</a>
                    <a class="<?= $activePage === 'settings' ? 'active fw-semibold' : '' ?>" href="<?= e(url('admin/settings.php')) ?>">Site Content</a>
                    <a class="<?= $activePage === 'account' ? 'active fw-semibold' : '' ?>" href="<?= e(url('admin/account.php')) ?>">My Account</a>
                    <?php if (is_super_admin()): ?>
                        <a class="<?= $activePage === 'admins' ? 'active fw-semibold' : '' ?>" href="<?= e(url('admin/admins.php')) ?>">Admins</a>
                    <?php endif; ?>
                    <a href="<?= e(url('admin/logout.php')) ?>">Sign Out</a>
                </nav>
            </aside>
            <main class="col-lg-10 px-4 px-lg-5 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <p class="section-eyebrow mb-2">Admin Dashboard</p>
                        <h1 class="h3 mb-0"><?= e($title) ?></h1>
                    </div>
                </div>
    <?php
}

function admin_footer(): void
{
    ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
