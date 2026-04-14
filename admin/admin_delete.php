<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_super_admin();

if (!request_method_is('POST')) {
    redirect('admin/admins.php');
}

$adminId = (int) ($_POST['id'] ?? 0);
$targetAdmin = $adminId > 0 ? fetch_admin_by_id($adminId) : null;

if ($targetAdmin === null) {
    flash('error', 'The requested admin account could not be found.');
    redirect('admin/admins.php');
}

if ((int) $targetAdmin['id'] === current_admin_id()) {
    flash('error', 'You cannot delete your own account from the admin list.');
    redirect('admin/admins.php');
}

if ($targetAdmin['role'] === 'super_admin' && count_super_admins() <= 1) {
    flash('error', 'You cannot delete the final major admin account.');
    redirect('admin/admins.php');
}

$reassign = db()->prepare('UPDATE properties SET admin_id = :new_admin_id WHERE admin_id = :old_admin_id');
$reassign->execute([
    'new_admin_id' => current_admin_id(),
    'old_admin_id' => $adminId,
]);

$delete = db()->prepare('DELETE FROM admins WHERE id = :id');
$delete->execute(['id' => $adminId]);

flash('success', 'Admin removed successfully. Their listings were reassigned to your account.');
redirect('admin/admins.php');
