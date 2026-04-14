<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$featuredProperties = fetch_properties(['featured_only' => true], 6);
$allProperties = fetch_properties();

render_header('Luxury Service, Local Expertise', 'home');
?>
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <p class="section-eyebrow text-warning-emphasis mb-3"><?= e(setting('hero_eyebrow', 'Premier Real Estate Agency')) ?></p>
                <h1 class="display-5 mb-4"><?= e(setting('hero_title', 'Find a home that feels effortless from the first tour to the final signature.')) ?></h1>
                <p class="lead text-white-50 mb-4"><?= e(setting('hero_subtitle', 'We market exceptional homes, guide smart investments, and give buyers a calm, informed experience in every neighborhood we serve.')) ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-gold btn-lg" href="<?= e(url('properties.php')) ?>">Browse Listings</a>
                    <button class="btn btn-outline-light btn-lg" data-scroll-target="#featured-listings">Featured Homes</button>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="hero-stat">
                            <div class="fs-2 fw-bold"><?= count($allProperties) ?></div>
                            <div class="text-white-50">Published listings</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hero-stat">
                            <div class="fs-2 fw-bold"><?= e(setting('years_experience', '12+')) ?></div>
                            <div class="text-white-50">Years of market knowledge</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="hero-stat">
                            <div class="fs-3 fw-bold"><?= e(setting('service_regions', 'Miami, Fort Lauderdale, Palm Beach')) ?></div>
                            <div class="text-white-50">Core service regions</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="card search-card">
        <div class="card-body p-4">
            <form action="<?= e(url('properties.php')) ?>" method="get" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">Search by city or address</label>
                    <input type="text" name="search" class="form-control form-control-lg" placeholder="Miami Beach, Brickell, Harbor Ave">
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Property type</label>
                    <select name="property_type" class="form-select form-select-lg">
                        <option value="">Any type</option>
                        <?php foreach (property_types() as $type): ?>
                            <option value="<?= e($type) ?>"><?= e($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Listing status</label>
                    <select name="status" class="form-select form-select-lg">
                        <option value="">Any status</option>
                        <?php foreach (property_statuses() as $status): ?>
                            <option value="<?= e($status) ?>"><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 d-grid">
                    <button class="btn btn-dark btn-lg" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>
</div>

<section class="py-5 mt-4" id="featured-listings">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="section-eyebrow mb-2">Featured Listings</p>
                <h2 class="mb-0">Homes buyers ask about first</h2>
            </div>
            <a class="btn btn-outline-dark" href="<?= e(url('properties.php')) ?>">View All Properties</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredProperties as $property): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card property-card h-100 overflow-hidden">
                        <img src="<?= e($property['display_image_url']) ?>" alt="<?= e($property['title']) ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge text-bg-<?= e(property_status_class($property['status'])) ?>"><?= e($property['status']) ?></span>
                                <span class="feature-pill"><?= e($property['property_type']) ?></span>
                            </div>
                            <h3 class="h4"><?= e($property['title']) ?></h3>
                            <p class="text-secondary mb-2"><?= e($property['address']) ?>, <?= e($property['city']) ?>, <?= e($property['state']) ?></p>
                            <div class="small text-secondary mb-3">Posted <?= e($property['posted_date']) ?></div>
                            <div class="property-meta mb-3">
                                <span><?= e((string) $property['bedrooms']) ?> Bedrooms</span>
                                <span><?= e(format_decimal($property['toilets'], 1)) ?> Toilets</span>
                                <span><?= e(format_area_sqm($property['building_area_sqm'])) ?> interior</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="property-price"><?= e(format_currency($property['price'])) ?></span>
                                <a class="btn btn-dark" href="<?= e(url('property.php?slug=' . urlencode($property['slug']))) ?>">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="card content-card p-4 p-lg-5">
                    <p class="section-eyebrow mb-2">Why Clients Choose Us</p>
                    <h2 class="mb-3"><?= e(setting('about_title', 'Sharp marketing, honest advice, and service that stays personal.')) ?></h2>
                    <p class="text-secondary mb-4"><?= e(setting('about_body', 'From staging guidance and photography to negotiation strategy and closing support, we handle the details that make listings stand out and transactions move smoothly.')) ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="feature-pill">Professional photography</span>
                        <span class="feature-pill">Neighborhood expertise</span>
                        <span class="feature-pill">Responsive communication</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card content-card p-4 p-lg-5 bg-dark text-white">
                    <p class="section-eyebrow mb-2">Contact the Team</p>
                    <h2 class="mb-3">Ready to buy, sell, or reposition a listing?</h2>
                    <p class="text-white-50 mb-4">Tell us what you are looking for and we will help you map the next step with clarity.</p>
                    <div class="mb-2"><?= e(setting('office_phone', '(305) 555-0189')) ?></div>
                    <div class="mb-2"><?= e(setting('office_email', 'hello@harborhomes.com')) ?></div>
                    <div class="mb-4"><?= e(setting('office_address', '18 Harbor Avenue, Miami, FL')) ?></div>
                    <a class="btn btn-gold" href="<?= e(url('contact.php')) ?>">Visit Contact Page</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php render_footer(); ?>
