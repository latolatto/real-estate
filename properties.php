<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'property_type' => trim((string) ($_GET['property_type'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
];

$properties = fetch_properties(array_filter($filters, static fn ($value) => $value !== ''));

render_header('Properties', 'properties');
?>
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="section-eyebrow mb-2">Available Properties</p>
                <h1 class="mb-0">Browse the listing portfolio</h1>
            </div>
            <div class="text-secondary"><?= count($properties) ?> result<?= count($properties) === 1 ? '' : 's' ?></div>
        </div>

        <div class="card search-card mb-4">
            <div class="card-body p-4">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="<?= e($filters['search']) ?>" placeholder="Address, city, neighborhood">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Property type</label>
                        <select class="form-select" name="property_type">
                            <option value="">Any type</option>
                            <?php foreach (property_types() as $type): ?>
                                <option value="<?= e($type) ?>" <?= $filters['property_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Any status</option>
                            <?php foreach (property_statuses() as $status): ?>
                                <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button class="btn btn-dark" type="submit">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($properties)): ?>
            <div class="empty-state">
                <h2 class="h4">No properties matched your filters</h2>
                <p class="text-secondary mb-0">Try widening the search or clearing one of the filters above.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card property-card h-100 overflow-hidden">
                            <img src="<?= e($property['display_image_url']) ?>" alt="<?= e($property['title']) ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge text-bg-<?= e(property_status_class($property['status'])) ?>"><?= e($property['status']) ?></span>
                                    <span class="feature-pill"><?= e($property['property_type']) ?></span>
                                </div>
                                <h2 class="h4"><?= e($property['title']) ?></h2>
                                <p class="text-secondary mb-2"><?= e($property['address']) ?>, <?= e($property['city']) ?>, <?= e($property['state']) ?></p>
                                <div class="small text-secondary mb-3">Posted <?= e($property['posted_date']) ?></div>
                                <div class="property-meta mb-3">
                                    <span><?= e((string) $property['bedrooms']) ?> Bedrooms</span>
                                    <span><?= e(format_decimal($property['toilets'], 1)) ?> Toilets</span>
                                    <span><?= e(format_area_sqm($property['building_area_sqm'])) ?> interior</span>
                                    <span><?= e(format_area_sqm($property['total_area_sqm'])) ?> total</span>
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
        <?php endif; ?>
    </div>
</section>
<?php render_footer(); ?>
