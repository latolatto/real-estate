<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_admin();

$properties = fetch_properties(['restrict_to_owner' => true]);
$featuredCount = count(array_filter($properties, static fn (array $property): bool => (int) $property['is_featured'] === 1));
$adminCount = is_super_admin() ? count(fetch_admins()) : null;

admin_header('Overview', 'dashboard');
?>
<?php if ($flash = flash('success')): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($flash = flash('warning')): ?>
    <div class="alert alert-warning"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($flash = flash('error')): ?>
    <div class="alert alert-danger"><?= e($flash) ?></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card admin-card p-4">
            <p class="text-secondary mb-2"><?= is_super_admin() ? 'Total listings' : 'Your listings' ?></p>
            <h2 class="display-6 mb-0"><?= count($properties) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card admin-card p-4">
            <p class="text-secondary mb-2">Featured listings</p>
            <h2 class="display-6 mb-0"><?= $featuredCount ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card admin-card p-4">
            <p class="text-secondary mb-2"><?= is_super_admin() ? 'Admin accounts' : 'Your role' ?></p>
            <h2 class="h4 mb-0"><?= is_super_admin() ? (string) $adminCount : e(admin_role_label(admin_user()['role'] ?? 'admin')) ?></h2>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h4 mb-1"><?= is_super_admin() ? 'Latest listings across the agency' : 'Your latest listings' ?></h2>
                    <p class="text-secondary mb-0">Quick snapshot of the property inventory you can manage.</p>
                </div>
                <a class="btn btn-dark" href="<?= e(url('admin/property_form.php')) ?>">Add Listing</a>
            </div>
            <?php if (empty($properties)): ?>
                <div class="empty-state">
                    <h3 class="h5">No listings yet</h3>
                    <p class="text-secondary mb-3">Create the first property listing to populate the public site.</p>
                    <a class="btn btn-dark" href="<?= e(url('admin/property_form.php')) ?>">Create Listing</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Property</th>
                            <?php if (is_super_admin()): ?>
                                <th>Owner</th>
                            <?php endif; ?>
                            <th>Posted</th>
                            <th>Price</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($properties, 0, 5) as $property): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($property['title']) ?></div>
                                    <div class="small text-secondary"><?= e($property['city']) ?>, <?= e($property['state']) ?></div>
                                </td>
                                <?php if (is_super_admin()): ?>
                                    <td><?= e($property['owner_name']) ?></td>
                                <?php endif; ?>
                                <td><?= e($property['posted_date']) ?></td>
                                <td><?= e(format_currency($property['price'])) ?></td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="<?= e(url('admin/property_form.php?id=' . $property['id'])) ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card admin-card p-4 mb-4">
            <h2 class="h4 mb-3">Account</h2>
            <p class="text-secondary">Update your email or password from your account settings page.</p>
            <a class="btn btn-outline-dark" href="<?= e(url('admin/account.php')) ?>">Manage Account</a>
        </div>
        <div class="card admin-card p-4">
            <h2 class="h4 mb-3">Content controls</h2>
            <p class="text-secondary">Update the homepage headline, office details, footer copy, and other reusable site content from one place.</p>
            <a class="btn btn-outline-dark" href="<?= e(url('admin/settings.php')) ?>">Edit Site Content</a>
        </div>
    </div>
</div>
<?php admin_footer(); ?>
