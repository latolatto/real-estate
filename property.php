<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$property = $slug !== '' ? fetch_property_by_slug($slug) : null;

if ($property === null) {
    http_response_code(404);
    render_header('Property Not Found', 'properties');
    ?>
    <section class="py-5">
        <div class="container">
            <div class="empty-state">
                <h1 class="h3">Property not found</h1>
                <p class="text-secondary">The listing you are looking for is unavailable or has been removed.</p>
                <a class="btn btn-dark" href="<?= e(url('properties.php')) ?>">Back to Listings</a>
            </div>
        </div>
    </section>
    <?php
    render_footer();
    exit;
}

$images = fetch_property_images((int) $property['id']);
$primaryImageUrl = $images !== [] ? $images[0]['public_url'] : $property['display_image_url'];
$galleryImages = $images !== [] ? array_slice($images, 1) : [];

render_header($property['title'], 'properties');
?>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <img class="property-hero-image rounded-4 shadow-sm mb-4" src="<?= e($primaryImageUrl) ?>" alt="<?= e($property['title']) ?>">
                <?php if ($galleryImages !== []): ?>
                    <div class="row g-3 mb-4">
                        <?php foreach ($galleryImages as $image): ?>
                            <div class="col-6 col-md-4">
                                <img src="<?= e($image['public_url']) ?>" class="w-100 rounded-4 shadow-sm property-gallery-image" alt="<?= e($property['title']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge text-bg-<?= e(property_status_class($property['status'])) ?>"><?= e($property['status']) ?></span>
                    <span class="feature-pill"><?= e($property['property_type']) ?></span>
                    <?php if ((int) $property['is_featured'] === 1): ?>
                        <span class="feature-pill">Featured</span>
                    <?php endif; ?>
                </div>
                <h1 class="mb-3"><?= e($property['title']) ?></h1>
                <p class="text-secondary fs-5"><?= e($property['address']) ?>, <?= e($property['city']) ?>, <?= e($property['state']) ?> <?= e($property['zip_code']) ?></p>
                <div class="small text-secondary mb-4">Posted <?= e($property['posted_date']) ?></div>
                <div class="property-meta fs-6 mb-4">
                    <span><?= e((string) $property['bedrooms']) ?> Bedrooms</span>
                    <span><?= e(format_decimal($property['toilets'], 1)) ?> Toilets</span>
                    <span><?= e(format_area_sqm($property['building_area_sqm'])) ?> interior</span>
                    <span><?= e(format_area_sqm($property['total_area_sqm'])) ?> total surface</span>
                </div>
                <div class="card content-card p-4">
                    <h2 class="h4 mb-3">Property Overview</h2>
                    <p class="mb-0 text-secondary"><?= nl2br(e($property['description'])) ?></p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card contact-card p-4 mb-4">
                    <p class="section-eyebrow mb-2">Listing Price</p>
                    <div class="property-price mb-3"><?= e(format_currency($property['price'])) ?></div>
                    <p class="text-secondary mb-4">Interested in a showing, valuation, or an offer strategy conversation? Reach the agency directly below.</p>
                    <div class="mb-2"><strong>Phone:</strong> <?= e(setting('office_phone', '(305) 555-0189')) ?></div>
                    <div class="mb-2"><strong>Email:</strong> <?= e(setting('office_email', 'hello@harborhomes.com')) ?></div>
                    <div class="mb-4"><strong>Office:</strong> <?= e(setting('office_address', '18 Harbor Avenue, Miami, FL')) ?></div>
                    <a class="btn btn-gold w-100" href="<?= e(url('contact.php')) ?>">Contact Agency</a>
                </div>
                <div class="card content-card p-4">
                    <h2 class="h5 mb-3">At a glance</h2>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Status</span><strong><?= e($property['status']) ?></strong></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Type</span><strong><?= e($property['property_type']) ?></strong></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Bedrooms</span><strong><?= e((string) $property['bedrooms']) ?></strong></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Toilets</span><strong><?= e(format_decimal($property['toilets'], 1)) ?></strong></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Building area</span><strong><?= e(format_area_sqm($property['building_area_sqm'])) ?></strong></div>
                    <div class="d-flex justify-content-between border-bottom py-2"><span>Total surface</span><strong><?= e(format_area_sqm($property['total_area_sqm'])) ?></strong></div>
                    <div class="d-flex justify-content-between py-2"><span>Posted</span><strong><?= e($property['posted_date']) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php render_footer(); ?>
