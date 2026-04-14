<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/_layout.php';

require_super_admin();

$adminId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editing = $adminId > 0;
$targetAdmin = $editing ? fetch_admin_by_id($adminId) : null;

if ($editing && $targetAdmin === null) {
    flash('error', 'The requested admin account could not be found.');
    redirect('admin/admins.php');
}

$values = $targetAdmin ?: [
    'name' => '',
    'email' => '',
    'role' => 'admin',
];

$errors = [];

if (request_method_is('POST')) {
    $values = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'role' => trim((string) ($_POST['role'] ?? 'admin')),
    ];
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($values['name'] === '') {
        $errors[] = 'Name is required.';
    }

    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    } elseif (fetch_admin_by_email($values['email'], $editing ? $adminId : null) !== null) {
        $errors[] = 'That email address is already in use.';
    }

    if (!array_key_exists($values['role'], admin_roles())) {
        $errors[] = 'Choose a valid admin role.';
    }

    if (!$editing && $newPassword === '') {
        $errors[] = 'A password is required for new admin accounts.';
    }

    if ($newPassword !== '' && strlen($newPassword) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (
        $editing
        && (int) $targetAdmin['id'] === current_admin_id()
        && $targetAdmin['role'] === 'super_admin'
        && $values['role'] !== 'super_admin'
        && count_super_admins() <= 1
    ) {
        $errors[] = 'You cannot remove the final major admin role from your own account.';
    }

    if (empty($errors)) {
        if ($editing) {
            $params = [
                'id' => $adminId,
                'name' => $values['name'],
                'email' => $values['email'],
                'role' => $values['role'],
            ];

            $sql = 'UPDATE admins SET name = :name, email = :email, role = :role';

            if ($newPassword !== '') {
                $sql .= ', password_hash = :password_hash';
                $params['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = :id';

            $stmt = db()->prepare($sql);
            $stmt->execute($params);

            if ($adminId === current_admin_id()) {
                $updatedAdmin = fetch_admin_by_id($adminId);
                if ($updatedAdmin !== null) {
                    set_admin_session($updatedAdmin);
                }
            }

            flash('success', 'Admin account updated successfully.');
        } else {
            $stmt = db()->prepare('INSERT INTO admins (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
            $stmt->execute([
                'name' => $values['name'],
                'email' => $values['email'],
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'role' => $values['role'],
            ]);

            flash('success', 'Admin account created successfully.');
        }

        redirect('admin/admins.php');
    }
}

admin_header($editing ? 'Edit Admin' : 'Add Admin', 'admins');
?>
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
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select class="form-select" name="role">
                <?php foreach (admin_roles() as $role => $label): ?>
                    <option value="<?= e($role) ?>" <?= $values['role'] === $role ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label"><?= $editing ? 'New password (optional)' : 'Password' ?></label>
            <input type="password" class="form-control" name="new_password" <?= $editing ? '' : 'required' ?>>
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm password</label>
            <input type="password" class="form-control" name="confirm_password" <?= $editing ? '' : 'required' ?>>
        </div>
        <div class="col-12">
            <button class="btn btn-dark" type="submit"><?= $editing ? 'Save Admin' : 'Create Admin' ?></button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/admins.php')) ?>">Cancel</a>
        </div>
    </form>
</div>
<?php admin_footer(); ?>
