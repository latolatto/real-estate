<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_admin();

$settingFields = [
    'hero_eyebrow' => 'Hero eyebrow',
    'hero_title' => 'Hero title',
    'hero_subtitle' => 'Hero subtitle',
    'about_title' => 'About title',
    'about_body' => 'About body',
    'service_regions' => 'Service regions',
    'years_experience' => 'Years experience',
    'office_phone' => 'Office phone',
    'office_email' => 'Office email',
    'office_address' => 'Office address',
    'office_hours' => 'Office hours',
    'footer_blurb' => 'Footer blurb',
];

$values = [];
foreach ($settingFields as $key => $label) {
    $values[$key] = setting($key);
}

if (request_method_is('POST')) {
    $stmt = db()->prepare('UPDATE site_settings SET setting_value = :value WHERE setting_key = :key');

    foreach ($settingFields as $key => $label) {
        $value = trim((string) ($_POST[$key] ?? ''));
        $stmt->execute([
            'value' => $value,
            'key' => $key,
        ]);
        $values[$key] = $value;
    }

    flash('success', 'Site content updated successfully.');
    redirect('admin/settings.php');
}

admin_header('Site Content', 'settings');
?>
<?php if ($flash = flash('success')): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>

<div class="card admin-card p-4">
    <form method="post" class="row g-4">
        <?php foreach ($settingFields as $key => $label): ?>
            <div class="col-md-6">
                <label class="form-label"><?= e($label) ?></label>
                <?php if (in_array($key, ['hero_subtitle', 'about_body', 'footer_blurb'], true)): ?>
                    <textarea class="form-control" name="<?= e($key) ?>" rows="4"><?= e($values[$key]) ?></textarea>
                <?php else: ?>
                    <input type="text" class="form-control" name="<?= e($key) ?>" value="<?= e($values[$key]) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="col-12">
            <button class="btn btn-dark" type="submit">Save Content</button>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
