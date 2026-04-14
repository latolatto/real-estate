<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_admin();

$properties = fetch_properties(['restrict_to_owner' => true]);

admin_header('Properties', 'properties');
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

<div class="card admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1"><?= is_super_admin() ? 'Manage all listings' : 'Manage your listings' ?></h2>
            <p class="text-secondary mb-0">
                <?= is_super_admin()
                    ? 'As the major admin, you can review, edit, or remove every property in the system.'
                    : 'You can only create, edit, and remove properties that belong to your account.' ?>
            </p>
        </div>
        <a class="btn btn-dark" href="<?= e(url('admin/property_form.php')) ?>">Add Listing</a>
    </div>

    <?php if (empty($properties)): ?>
        <div class="empty-state">
            <h3 class="h5">No listings available</h3>
            <p class="text-secondary mb-3">Add a property to get started.</p>
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
                    <th>Interior</th>
                    <th>Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($property['title']) ?></div>
                            <div class="small text-secondary">
                                <?= e((string) $property['bedrooms']) ?> bedrooms,
                                <?= e(format_decimal($property['toilets'], 1)) ?> toilets
                            </div>
                        </td>
                        <?php if (is_super_admin()): ?>
                            <td><?= e($property['owner_name']) ?></td>
                        <?php endif; ?>
                        <td><?= e($property['posted_date']) ?></td>
                        <td><?= e(format_currency($property['price'])) ?></td>
                        <td><?= e(format_area_sqm($property['building_area_sqm'])) ?></td>
                        <td><?= e(format_area_sqm($property['total_area_sqm'])) ?></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-dark" href="<?= e(url('admin/property_form.php?id=' . $property['id'])) ?>">Edit</a>
                                <form method="post" action="<?= e(url('admin/property_delete.php')) ?>" onsubmit="return confirm('Delete this listing?');">
                                    <input type="hidden" name="id" value="<?= e((string) $property['id']) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php admin_footer(); ?>
