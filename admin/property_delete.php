<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

require_admin();

if (!request_method_is('POST')) {
    redirect('admin/properties.php');
}

$id = (int) ($_POST['id'] ?? 0);
$property = $id > 0 ? fetch_property_by_id($id) : null;

if ($property === null) {
    flash('error', 'The requested listing could not be found.');
    redirect('admin/properties.php');
}

if (!can_manage_property($property)) {
    flash('error', 'You are not allowed to delete that listing.');
    redirect('admin/properties.php');
}

delete_property($id);
flash('success', 'Listing deleted successfully.');

redirect('admin/properties.php');
