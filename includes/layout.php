<?php

declare(strict_types=1);

function render_header(string $title, string $activePage = ''): void
{
    $fullTitle = $title . ' | ' . site_name();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($fullTitle) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?= e(url('assets/css/site.css')) ?>" rel="stylesheet">
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-dark agency-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= e(url()) ?>"><?= e(site_name()) ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="<?= e(url()) ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'properties' ? 'active' : '' ?>" href="<?= e(url('properties.php')) ?>">Properties</a></li>
                    <li class="nav-item"><a class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>" href="<?= e(url('contact.php')) ?>">Contact</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-gold" href="<?= e(url('admin/login.php')) ?>">Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}

function render_footer(): void
{
    ?>
    <footer class="site-footer py-5 mt-5">
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-5">
                    <p class="footer-brand mb-2"><?= e(site_name()) ?></p>
                    <p class="text-white-50 mb-0"><?= e(setting('footer_blurb', 'Boutique real estate guidance for buyers, sellers, and investors who want thoughtful service and standout listings.')) ?></p>
                </div>
                <div class="col-lg-3">
                    <h6 class="text-uppercase text-white-50">Office</h6>
                    <p class="mb-1"><?= e(setting('office_address', '18 Harbor Avenue, Miami, FL')) ?></p>
                    <p class="mb-1"><?= e(setting('office_phone', '(305) 555-0189')) ?></p>
                    <p class="mb-0"><?= e(setting('office_email', 'hello@harborhomes.com')) ?></p>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-uppercase text-white-50">Hours</h6>
                    <p class="mb-0"><?= e(setting('office_hours', 'Mon - Sat: 9:00 AM - 6:00 PM')) ?></p>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(url('assets/js/site.js')) ?>"></script>
    </body>
    </html>
    <?php
}
