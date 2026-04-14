<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_super_admin();

$admins = fetch_admins();

admin_header('Admins', 'admins');
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
            <h2 class="h4 mb-1">Manage admin accounts</h2>
            <p class="text-secondary mb-0">Create and remove admins, and manage who has major admin permissions.</p>
        </div>
        <a class="btn btn-dark" href="<?= e(url('admin/admin_form.php')) ?>">Add Admin</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Listings</th>
                <th>Created</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e($admin['name']) ?></div>
                        <?php if ((int) $admin['id'] === current_admin_id()): ?>
                            <div class="small text-secondary">You</div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($admin['email']) ?></td>
                    <td><?= e(admin_role_label($admin['role'])) ?></td>
                    <td><?= e((string) $admin['property_count']) ?></td>
                    <td><?= e(format_posted_date($admin['created_at'])) ?></td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-sm btn-outline-dark" href="<?= e(url('admin/admin_form.php?id=' . $admin['id'])) ?>">Edit</a>
                            <?php if ((int) $admin['id'] !== current_admin_id()): ?>
                                <form method="post" action="<?= e(url('admin/admin_delete.php')) ?>" onsubmit="return confirm('Delete this admin? Their properties will be reassigned to you.');">
                                    <input type="hidden" name="id" value="<?= e((string) $admin['id']) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_footer(); ?>
