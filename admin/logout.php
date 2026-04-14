<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

logout_admin();
flash('success', 'You have been signed out.');
redirect('admin/login.php');
