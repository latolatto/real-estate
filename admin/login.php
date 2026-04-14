<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

if (admin_user() !== null) {
    redirect('admin/index.php');
}

$error = null;

if (request_method_is('POST')) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (authenticate_admin($email, $password)) {
        flash('success', 'Welcome back. You are now signed in.');
        redirect('admin/index.php');
    }

    $error = 'The email and password combination did not match our records.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e('Admin Sign In | ' . site_name()) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/site.css')) ?>" rel="stylesheet">
</head>
<body class="admin-shell d-flex align-items-center">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card admin-card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <p class="section-eyebrow mb-2">Admin Access</p>
                    <h1 class="h3 mb-3">Sign in to manage listings</h1>
                    <p class="text-secondary mb-4">Use the seeded admin credentials from the setup guide, then change them in your database after first login.</p>
                    <?php if ($flash = flash('error')): ?>
                        <div class="alert alert-danger"><?= e($flash) ?></div>
                    <?php endif; ?>
                    <?php if ($flash = flash('success')): ?>
                        <div class="alert alert-success"><?= e($flash) ?></div>
                    <?php endif; ?>
                    <?php if ($error !== null): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="post" class="d-grid gap-3">
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" value="<?= e(old('email')) ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg" required>
                        </div>
                        <button class="btn btn-dark btn-lg" type="submit">Sign In</button>
                    </form>
                    <div class="mt-4">
                        <a href="<?= e(url()) ?>" class="text-decoration-none">Back to website</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
