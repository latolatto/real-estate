<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_admin();

$propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editing = $propertyId > 0;
$property = $editing ? fetch_property_by_id($propertyId) : null;

if ($editing && ($property === null || !can_manage_property($property))) {
    flash('error', 'The requested listing could not be found or you do not have permission to edit it.');
    redirect('admin/properties.php');
}

$values = $property ?: [
    'title' => '',
    'price' => '',
    'property_type' => 'House',
    'status' => 'For Sale',
    'bedrooms' => '',
    'toilets' => '',
    'building_area_sqm' => '',
    'total_area_sqm' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'zip_code' => '',
    'description' => '',
    'is_featured' => 0,
];

$existingImages = $editing ? fetch_property_images($propertyId) : [];
$errors = [];

if (request_method_is('POST')) {
    $values = [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'price' => trim((string) ($_POST['price'] ?? '')),
        'property_type' => trim((string) ($_POST['property_type'] ?? '')),
        'status' => trim((string) ($_POST['status'] ?? '')),
        'bedrooms' => trim((string) ($_POST['bedrooms'] ?? '')),
        'toilets' => trim((string) ($_POST['toilets'] ?? '')),
        'building_area_sqm' => trim((string) ($_POST['building_area_sqm'] ?? '')),
        'total_area_sqm' => trim((string) ($_POST['total_area_sqm'] ?? '')),
        'address' => trim((string) ($_POST['address'] ?? '')),
        'city' => trim((string) ($_POST['city'] ?? '')),
        'state' => trim((string) ($_POST['state'] ?? '')),
        'zip_code' => trim((string) ($_POST['zip_code'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
    ];

    $errors = validate_property($values);
    $errors = array_merge($errors, validate_property_image_uploads($_FILES['images'] ?? []));

    $removeImageIds = array_values(array_unique(array_map('intval', $_POST['remove_images'] ?? [])));
    $validRemoveImageIds = [];

    foreach ($removeImageIds as $imageId) {
        if ($editing && property_image_belongs_to_property($propertyId, $imageId)) {
            $validRemoveImageIds[] = $imageId;
        }
    }

    $primaryImageId = null;
    if (isset($_POST['primary_image_id']) && $_POST['primary_image_id'] !== '') {
        $primaryImageId = (int) $_POST['primary_image_id'];

        if ($editing && !property_image_belongs_to_property($propertyId, $primaryImageId)) {
            $errors[] = 'Choose a valid primary image.';
        }
    }

    $remainingExistingCount = count(array_filter(
        $existingImages,
        static fn (array $image): bool => !in_array((int) $image['id'], $validRemoveImageIds, true)
    ));
    $pendingUploadsCount = count(normalize_uploaded_files($_FILES['images'] ?? []));

    if ($remainingExistingCount + $pendingUploadsCount === 0) {
        $errors[] = 'Please keep or upload at least one property image.';
    }

    if (empty($errors)) {
        $slug = generate_slug($values['title'], $editing ? $propertyId : null);

        if ($editing) {
            $sql = 'UPDATE properties SET
                title = :title,
                slug = :slug,
                price = :price,
                property_type = :property_type,
                status = :status,
                bedrooms = :bedrooms,
                toilets = :toilets,
                building_area_sqm = :building_area_sqm,
                total_area_sqm = :total_area_sqm,
                address = :address,
                city = :city,
                state = :state,
                zip_code = :zip_code,
                description = :description,
                is_featured = :is_featured
                WHERE id = :id';
        } else {
            $sql = 'INSERT INTO properties (
                admin_id, title, slug, price, property_type, status, bedrooms, toilets,
                building_area_sqm, total_area_sqm, address, city, state, zip_code, description, is_featured
            ) VALUES (
                :admin_id, :title, :slug, :price, :property_type, :status, :bedrooms, :toilets,
                :building_area_sqm, :total_area_sqm, :address, :city, :state, :zip_code, :description, :is_featured
            )';
        }

        $params = [
            'title' => $values['title'],
            'slug' => $slug,
            'price' => $values['price'],
            'property_type' => $values['property_type'],
            'status' => $values['status'],
            'bedrooms' => $values['bedrooms'],
            'toilets' => $values['toilets'],
            'building_area_sqm' => $values['building_area_sqm'],
            'total_area_sqm' => $values['total_area_sqm'],
            'address' => $values['address'],
            'city' => $values['city'],
            'state' => $values['state'],
            'zip_code' => $values['zip_code'],
            'description' => $values['description'],
            'is_featured' => $values['is_featured'],
        ];

        if ($editing) {
            $params['id'] = $propertyId;
        } else {
            $params['admin_id'] = current_admin_id();
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        if (!$editing) {
            $propertyId = (int) db()->lastInsertId();
        }

        foreach ($validRemoveImageIds as $imageId) {
            delete_property_image($imageId);
        }

        $uploadResult = store_uploaded_property_images($propertyId, $_FILES['images'] ?? []);
        $preferredPrimaryImageId = null;

        if ($primaryImageId !== null && !in_array($primaryImageId, $validRemoveImageIds, true)) {
            $preferredPrimaryImageId = $primaryImageId;
        } elseif (!empty($uploadResult['saved_ids'])) {
            $preferredPrimaryImageId = $uploadResult['saved_ids'][0];
        }

        ensure_property_primary_image($propertyId, $preferredPrimaryImageId);

        if (!property_has_images($propertyId)) {
            if (!$editing) {
                delete_property($propertyId);
            }

            $errors[] = 'At least one image must upload successfully.';
        } else {
            if ($uploadResult['errors']) {
                flash('warning', implode(' ', $uploadResult['errors']));
            }

            flash('success', $editing ? 'Listing updated successfully.' : 'Listing created successfully.');
            redirect('admin/properties.php');
        }
    }

    $existingImages = $editing ? fetch_property_images($propertyId) : [];
}

admin_header($editing ? 'Edit Property' : 'Add Property', 'properties');
?>
<?php if ($flash = flash('error')): ?>
    <div class="alert alert-danger"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (array_unique($errors) as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card admin-card p-4">
    <form method="post" enctype="multipart/form-data" class="row g-4">
        <?php if ($editing && is_super_admin()): ?>
            <div class="col-12">
                <div class="alert alert-light border">
                    Owned by <strong><?= e($property['owner_name']) ?></strong> (<?= e($property['owner_email']) ?>)
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-8">
            <label class="form-label">Property title</label>
            <input type="text" class="form-control" name="title" value="<?= e($values['title']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Price</label>
            <input type="number" class="form-control" step="1000" name="price" value="<?= e((string) $values['price']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Property type</label>
            <select class="form-select" name="property_type">
                <?php foreach (property_types() as $type): ?>
                    <option value="<?= e($type) ?>" <?= $values['property_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <?php foreach (property_statuses() as $status): ?>
                    <option value="<?= e($status) ?>" <?= $values['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Bedrooms</label>
            <input type="number" class="form-control" step="1" name="bedrooms" value="<?= e((string) $values['bedrooms']) ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Toilets</label>
            <input type="number" class="form-control" step="0.5" name="toilets" value="<?= e((string) $values['toilets']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Building area (sq. m.)</label>
            <input type="number" class="form-control" step="0.1" name="building_area_sqm" value="<?= e((string) $values['building_area_sqm']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Total surface (sq. m.)</label>
            <input type="number" class="form-control" step="0.1" name="total_area_sqm" value="<?= e((string) $values['total_area_sqm']) ?>" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= e($values['address']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" value="<?= e($values['city']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input type="text" class="form-control" name="state" value="<?= e($values['state']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">ZIP code</label>
            <input type="text" class="form-control" name="zip_code" value="<?= e($values['zip_code']) ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="6" required><?= e($values['description']) ?></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Upload photos</label>
            <input type="file" class="form-control" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple <?= $editing ? '' : 'required' ?>>
            <div class="form-text">You can upload multiple JPG, PNG, or WEBP files up to 5 MB each.</div>
        </div>

        <?php if (!empty($existingImages)): ?>
            <div class="col-12">
                <h2 class="h5 mb-3">Existing gallery</h2>
                <div class="row g-3">
                    <?php foreach ($existingImages as $image): ?>
                        <div class="col-md-4 col-xl-3">
                            <div class="card h-100">
                                <img src="<?= e($image['public_url']) ?>" class="card-img-top" alt="Property image" style="height: 180px; object-fit: cover;">
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="primary_image_id" id="primary_<?= e((string) $image['id']) ?>" value="<?= e((string) $image['id']) ?>" <?= (int) $image['is_primary'] === 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="primary_<?= e((string) $image['id']) ?>">Primary photo</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_images[]" id="remove_<?= e((string) $image['id']) ?>" value="<?= e((string) $image['id']) ?>">
                                        <label class="form-check-label" for="remove_<?= e((string) $image['id']) ?>">Remove this image</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?= (int) $values['is_featured'] === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_featured">Mark as featured on homepage</label>
            </div>
        </div>
        <div class="col-12 d-flex gap-3">
            <button class="btn btn-dark" type="submit"><?= $editing ? 'Save Changes' : 'Create Listing' ?></button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/properties.php')) ?>">Cancel</a>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
