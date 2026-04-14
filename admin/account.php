<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_admin();

$admin = fetch_admin_by_id((int) current_admin_id());

if ($admin === null) {
    logout_admin();
    flash('error', 'Your account could not be loaded. Please sign in again.');
    redirect('admin/login.php');
}

$values = [
    'name' => $admin['name'],
    'email' => $admin['email'],
];

$errors = [];

if (request_method_is('POST')) {
    $values = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
    ];
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($values['name'] === '') {
        $errors[] = 'Name is required.';
    }

    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    } elseif (fetch_admin_by_email($values['email'], (int) $admin['id']) !== null) {
        $errors[] = 'That email address is already in use.';
    }

    $credentialsChanging = $values['email'] !== $admin['email'] || $newPassword !== '';

    if ($credentialsChanging && $currentPassword === '') {
        $errors[] = 'Enter your current password to change your email or password.';
    }

    if ($currentPassword !== '' && !admin_password_is_valid($admin, $currentPassword)) {
        $errors[] = 'Your current password is incorrect.';
    }

    if ($newPassword !== '' && strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters long.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if (empty($errors)) {
        $params = [
            'id' => (int) $admin['id'],
            'name' => $values['name'],
            'email' => $values['email'],
        ];

        $sql = 'UPDATE admins SET name = :name, email = :email';

        if ($newPassword !== '') {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        $admin = fetch_admin_by_id((int) $admin['id']);
        if ($admin !== null) {
            set_admin_session($admin);
        }

        flash('success', 'Your account details were updated.');
        redirect('admin/account.php');
    }
}

admin_header('My Account', 'account');
?>
<?php if ($flash = flash('success')): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
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
    <form method="post" class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" value="<?= e($values['name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= e($values['email']) ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Current password</label>
            <input type="password" class="form-control" name="current_password">
            <div class="form-text">Required when changing your email or setting a new password.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">New password</label>
            <input type="password" class="form-control" name="new_password">
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm new password</label>
            <input type="password" class="form-control" name="confirm_password">
        </div>
        <div class="col-12">
            <button class="btn btn-dark" type="submit">Save Account Changes</button>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
