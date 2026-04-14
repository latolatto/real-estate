<?php

declare(strict_types=1);

function app_config(): array
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/../config/database.php';
    }

    return $config;
}

function db(): PDO
{
    static $pdo;

    if ($pdo === null) {
        $config = app_config();
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        $pdo = new PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}

function site_name(): string
{
    return app_config()['app_name'];
}

function base_url(): string
{
    $configured = app_config()['base_url'];

    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($scriptName === '') {
        return '';
    }

    $directory = str_replace('\\', '/', dirname($scriptName));
    $directory = $directory === '/' ? '' : rtrim($directory, '/');

    if (str_ends_with($directory, '/admin')) {
        $directory = substr($directory, 0, -6);
    }

    return $directory;
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    if ($path === '') {
        return base_url() !== '' ? base_url() . '/' : '/';
    }

    return base_url() !== '' ? base_url() . '/' . $path : '/' . $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function request_method_is(string $method): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === strtoupper($method);
}

function old(string $key, string $default = ''): string
{
    return $_POST[$key] ?? $default;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $value;
}

function property_status_class(string $status): string
{
    return match ($status) {
        'For Rent' => 'warning',
        'Sold' => 'secondary',
        default => 'success',
    };
}

function format_currency(float|string $price): string
{
    return '$' . number_format((float) $price, 0);
}

function format_decimal(float|string $value, int $decimals = 1): string
{
    $formatted = number_format((float) $value, $decimals, '.', ',');
    return rtrim(rtrim($formatted, '0'), '.');
}

function format_area_sqm(float|string $area): string
{
    return format_decimal($area, 1) . ' sq. m.';
}

function format_posted_date(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }

    return date('M j, Y', strtotime($date));
}

function property_types(): array
{
    return ['House', 'Apartment', 'Condo', 'Villa', 'Townhouse', 'Land', 'Commercial'];
}

function property_statuses(): array
{
    return ['For Sale', 'For Rent', 'Sold'];
}

function admin_roles(): array
{
    return [
        'super_admin' => 'Major Admin',
        'admin' => 'Admin',
    ];
}

function admin_role_label(string $role): string
{
    return admin_roles()[$role] ?? 'Admin';
}

function &settings_store(): array
{
    static $store = ['loaded' => false, 'data' => []];

    return $store;
}

function fetch_settings(): array
{
    $store =& settings_store();

    if (!$store['loaded']) {
        $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        $store['data'] = [];

        foreach ($rows as $row) {
            $store['data'][$row['setting_key']] = $row['setting_value'];
        }
        $store['loaded'] = true;
    }

    return $store['data'];
}

function refresh_settings_cache(): void
{
    $store =& settings_store();
    $store = ['loaded' => false, 'data' => []];
}

function setting(string $key, string $default = ''): string
{
    $settings = fetch_settings();
    return $settings[$key] ?? $default;
}

function admin_user(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}

function current_admin_id(): ?int
{
    return isset($_SESSION['admin_user']['id']) ? (int) $_SESSION['admin_user']['id'] : null;
}

function is_super_admin(): bool
{
    return (admin_user()['role'] ?? '') === 'super_admin';
}

function set_admin_session(array $user): void
{
    $_SESSION['admin_user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'admin',
    ];
}

function fetch_admin_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $admin = $stmt->fetch();

    return $admin ?: null;
}

function fetch_admin_by_email(string $email, ?int $ignoreId = null): ?array
{
    $sql = 'SELECT * FROM admins WHERE email = :email';
    $params = ['email' => $email];

    if ($ignoreId !== null) {
        $sql .= ' AND id != :id';
        $params['id'] = $ignoreId;
    }

    $sql .= ' LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $admin = $stmt->fetch();

    return $admin ?: null;
}

function fetch_admins(): array
{
    $sql = 'SELECT a.*, COUNT(p.id) AS property_count
        FROM admins a
        LEFT JOIN properties p ON p.admin_id = a.id
        GROUP BY a.id
        ORDER BY CASE WHEN a.role = "super_admin" THEN 0 ELSE 1 END, a.name ASC';

    return db()->query($sql)->fetchAll();
}

function count_super_admins(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM admins WHERE role = "super_admin"')->fetchColumn();
}

function password_looks_hashed(string $value): bool
{
    return str_starts_with($value, '$2y$')
        || str_starts_with($value, '$argon2i$')
        || str_starts_with($value, '$argon2id$');
}

function sync_admin_password_hash(int $adminId, string $plainPassword): void
{
    $stmt = db()->prepare('UPDATE admins SET password_hash = :password_hash WHERE id = :id');
    $stmt->execute([
        'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
        'id' => $adminId,
    ]);
}

function admin_password_is_valid(array $user, string $password, bool $rehashIfNeeded = true): bool
{
    $storedPassword = (string) $user['password_hash'];
    $valid = false;
    $shouldRehash = false;

    if (password_looks_hashed($storedPassword)) {
        $valid = password_verify($password, $storedPassword);
        $shouldRehash = $valid && password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
    } else {
        $valid = hash_equals($storedPassword, $password);
        $shouldRehash = $valid;
    }

    if (!$valid) {
        return false;
    }

    if ($shouldRehash && $rehashIfNeeded) {
        sync_admin_password_hash((int) $user['id'], $password);
    }

    return true;
}

function authenticate_admin(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !admin_password_is_valid($user, $password)) {
        return false;
    }

    $user['role'] = $user['role'] ?? 'admin';
    set_admin_session($user);

    return true;
}

function logout_admin(): void
{
    unset($_SESSION['admin_user']);
}

function require_admin(): void
{
    if (admin_user() === null) {
        flash('error', 'Please sign in to access the admin dashboard.');
        redirect('admin/login.php');
    }
}

function require_super_admin(): void
{
    require_admin();

    if (!is_super_admin()) {
        flash('error', 'Only the major admin can access that section.');
        redirect('admin/index.php');
    }
}

function can_manage_property(array $property): bool
{
    return is_super_admin() || (int) $property['admin_id'] === current_admin_id();
}

function default_property_image(): string
{
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800">'
        . '<rect width="1200" height="800" fill="#18384d"/>'
        . '<circle cx="950" cy="180" r="110" fill="#d4a657" opacity="0.7"/>'
        . '<rect x="150" y="420" width="900" height="190" rx="24" fill="#f8f2e8"/>'
        . '<polygon points="220,430 420,260 620,430" fill="#d4a657"/>'
        . '<polygon points="430,430 670,210 920,430" fill="#c39045"/>'
        . '<rect x="310" y="470" width="170" height="140" rx="16" fill="#18384d"/>'
        . '<rect x="570" y="470" width="150" height="140" rx="16" fill="#18384d"/>'
        . '<text x="150" y="710" fill="#ffffff" font-size="54" font-family="Arial">Property Image</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function is_external_path(string $path): bool
{
    return preg_match('/^(?:https?:)?\/\//i', $path) === 1 || str_starts_with($path, 'data:');
}

function public_path_to_url(string $path): string
{
    if ($path === '') {
        return default_property_image();
    }

    if (is_external_path($path)) {
        return $path;
    }

    return url(ltrim($path, '/'));
}

function decorate_property_row(array $property): array
{
    $property['display_image_url'] = public_path_to_url((string) ($property['primary_image_path'] ?? ''));
    $property['posted_date'] = format_posted_date($property['created_at'] ?? null);

    return $property;
}

function property_select_sql(): string
{
    return 'SELECT p.*, a.name AS owner_name, a.email AS owner_email,
        COALESCE(pi.image_path, "") AS primary_image_path
        FROM properties p
        INNER JOIN admins a ON a.id = p.admin_id
        LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_primary = 1
        WHERE 1 = 1';
}

function fetch_properties(array $filters = [], ?int $limit = null): array
{
    $sql = property_select_sql();
    $params = [];

    if (!empty($filters['featured_only'])) {
        $sql .= ' AND p.is_featured = 1';
    }

    if (!empty($filters['status'])) {
        $sql .= ' AND p.status = :status';
        $params['status'] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (p.title LIKE :search OR p.city LIKE :search OR p.state LIKE :search OR p.address LIKE :search)';
        $params['search'] = '%' . $filters['search'] . '%';
    }

    if (!empty($filters['property_type'])) {
        $sql .= ' AND p.property_type = :property_type';
        $params['property_type'] = $filters['property_type'];
    }

    if (!empty($filters['admin_id'])) {
        $sql .= ' AND p.admin_id = :admin_id';
        $params['admin_id'] = (int) $filters['admin_id'];
    } elseif (!empty($filters['restrict_to_owner']) && !is_super_admin() && current_admin_id() !== null) {
        $sql .= ' AND p.admin_id = :admin_id';
        $params['admin_id'] = current_admin_id();
    }

    $sql .= ' ORDER BY p.is_featured DESC, p.created_at DESC';

    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int) $limit;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return array_map('decorate_property_row', $stmt->fetchAll());
}

function fetch_property_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(property_select_sql() . ' AND p.slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $property = $stmt->fetch();

    return $property ? decorate_property_row($property) : null;
}

function fetch_property_by_id(int $id): ?array
{
    $stmt = db()->prepare(property_select_sql() . ' AND p.id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $property = $stmt->fetch();

    return $property ? decorate_property_row($property) : null;
}

function fetch_property_images(int $propertyId): array
{
    $stmt = db()->prepare('SELECT * FROM property_images WHERE property_id = :property_id ORDER BY is_primary DESC, sort_order ASC, id ASC');
    $stmt->execute(['property_id' => $propertyId]);
    $images = $stmt->fetchAll();

    foreach ($images as &$image) {
        $image['public_url'] = public_path_to_url((string) $image['image_path']);
    }

    return $images;
}

function property_has_images(int $propertyId): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM property_images WHERE property_id = :property_id');
    $stmt->execute(['property_id' => $propertyId]);

    return (int) $stmt->fetchColumn() > 0;
}

function property_image_belongs_to_property(int $propertyId, int $imageId): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM property_images WHERE property_id = :property_id AND id = :image_id');
    $stmt->execute([
        'property_id' => $propertyId,
        'image_id' => $imageId,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function next_property_image_sort_order(int $propertyId): int
{
    $stmt = db()->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM property_images WHERE property_id = :property_id');
    $stmt->execute(['property_id' => $propertyId]);

    return (int) $stmt->fetchColumn();
}

function set_property_primary_image(int $propertyId, int $imageId): void
{
    $clear = db()->prepare('UPDATE property_images SET is_primary = 0 WHERE property_id = :property_id');
    $clear->execute(['property_id' => $propertyId]);

    $set = db()->prepare('UPDATE property_images SET is_primary = 1 WHERE property_id = :property_id AND id = :image_id');
    $set->execute([
        'property_id' => $propertyId,
        'image_id' => $imageId,
    ]);
}

function ensure_property_primary_image(int $propertyId, ?int $preferredImageId = null): void
{
    if ($preferredImageId !== null && property_image_belongs_to_property($propertyId, $preferredImageId)) {
        set_property_primary_image($propertyId, $preferredImageId);
        return;
    }

    $stmt = db()->prepare('SELECT COUNT(*) FROM property_images WHERE property_id = :property_id AND is_primary = 1');
    $stmt->execute(['property_id' => $propertyId]);

    if ((int) $stmt->fetchColumn() > 0) {
        return;
    }

    $stmt = db()->prepare('SELECT id FROM property_images WHERE property_id = :property_id ORDER BY sort_order ASC, id ASC LIMIT 1');
    $stmt->execute(['property_id' => $propertyId]);
    $imageId = $stmt->fetchColumn();

    if ($imageId !== false) {
        set_property_primary_image($propertyId, (int) $imageId);
    }
}

function property_upload_root_path(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'properties';
}

function ensure_directory_exists(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

function normalize_uploaded_files(array $files): array
{
    if (!isset($files['name']) || !is_array($files['name'])) {
        return [];
    }

    $normalized = [];

    foreach ($files['name'] as $index => $name) {
        $error = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $error,
            'size' => (int) ($files['size'][$index] ?? 0),
        ];
    }

    return $normalized;
}

function validate_property_image_uploads(array $files): array
{
    $uploads = normalize_uploaded_files($files);
    $errors = [];

    if ($uploads === []) {
        return [];
    }

    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    foreach ($uploads as $upload) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'One of the selected images could not be uploaded.';
            continue;
        }

        if ($upload['size'] > 5 * 1024 * 1024) {
            $errors[] = sprintf('Image "%s" is larger than 5 MB.', $upload['name']);
            continue;
        }

        $mime = (string) finfo_file($finfo, $upload['tmp_name']);

        if (!in_array($mime, $allowedTypes, true)) {
            $errors[] = sprintf('Image "%s" must be a JPG, PNG, or WEBP file.', $upload['name']);
        }
    }

    finfo_close($finfo);

    return array_values(array_unique($errors));
}

function store_uploaded_property_images(int $propertyId, array $files): array
{
    $uploads = normalize_uploaded_files($files);
    $savedIds = [];
    $errors = [];

    if ($uploads === []) {
        return ['saved_ids' => [], 'errors' => []];
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $propertyDirectory = property_upload_root_path() . DIRECTORY_SEPARATOR . 'property-' . $propertyId;
    ensure_directory_exists($propertyDirectory);

    foreach ($uploads as $upload) {
        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'One of the images could not be uploaded.';
            continue;
        }

        if ($upload['size'] > 5 * 1024 * 1024) {
            $errors[] = sprintf('Image "%s" is larger than 5 MB.', $upload['name']);
            continue;
        }

        $mime = (string) finfo_file($finfo, $upload['tmp_name']);
        $extension = $allowedTypes[$mime] ?? null;

        if ($extension === null) {
            $errors[] = sprintf('Image "%s" must be a JPG, PNG, or WEBP file.', $upload['name']);
            continue;
        }

        $filename = 'property-' . $propertyId . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $absolutePath = $propertyDirectory . DIRECTORY_SEPARATOR . $filename;
        $publicPath = '/uploads/properties/property-' . $propertyId . '/' . $filename;

        if (!move_uploaded_file($upload['tmp_name'], $absolutePath)) {
            $errors[] = sprintf('Image "%s" could not be saved.', $upload['name']);
            continue;
        }

        $stmt = db()->prepare('INSERT INTO property_images (property_id, image_path, sort_order, is_primary) VALUES (:property_id, :image_path, :sort_order, 0)');
        $stmt->execute([
            'property_id' => $propertyId,
            'image_path' => $publicPath,
            'sort_order' => next_property_image_sort_order($propertyId),
        ]);

        $savedIds[] = (int) db()->lastInsertId();
    }

    finfo_close($finfo);

    return ['saved_ids' => $savedIds, 'errors' => $errors];
}

function local_public_path_to_absolute(string $path): ?string
{
    if ($path === '' || is_external_path($path) || !str_starts_with($path, '/uploads/')) {
        return null;
    }

    return dirname(__DIR__) . str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function delete_property_image(int $imageId): void
{
    $stmt = db()->prepare('SELECT * FROM property_images WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $imageId]);
    $image = $stmt->fetch();

    if (!$image) {
        return;
    }

    $delete = db()->prepare('DELETE FROM property_images WHERE id = :id');
    $delete->execute(['id' => $imageId]);

    $absolutePath = local_public_path_to_absolute((string) $image['image_path']);

    if ($absolutePath !== null && is_file($absolutePath)) {
        unlink($absolutePath);
    }
}

function delete_property(int $propertyId): void
{
    $images = fetch_property_images($propertyId);

    foreach ($images as $image) {
        delete_property_image((int) $image['id']);
    }

    $stmt = db()->prepare('DELETE FROM properties WHERE id = :id');
    $stmt->execute(['id' => $propertyId]);
}

function generate_slug(string $title, ?int $ignoreId = null): string
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title) ?? '', '-'));
    $slug = $slug !== '' ? $slug : 'property';
    $candidate = $slug;
    $counter = 2;

    while (true) {
        $sql = 'SELECT COUNT(*) FROM properties WHERE slug = :slug';
        $params = ['slug' => $candidate];

        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        if ((int) $stmt->fetchColumn() === 0) {
            return $candidate;
        }

        $candidate = $slug . '-' . $counter;
        $counter++;
    }
}

function validate_property(array $input): array
{
    $errors = [];

    if (trim($input['title'] ?? '') === '') {
        $errors[] = 'Property title is required.';
    }

    if (!is_numeric($input['price'] ?? null)) {
        $errors[] = 'Property price must be numeric.';
    }

    foreach (['city', 'state', 'address', 'bedrooms', 'toilets', 'building_area_sqm', 'total_area_sqm', 'description'] as $field) {
        if (trim((string) ($input[$field] ?? '')) === '') {
            $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    if (!in_array($input['property_type'] ?? '', property_types(), true)) {
        $errors[] = 'Choose a valid property type.';
    }

    if (!in_array($input['status'] ?? '', property_statuses(), true)) {
        $errors[] = 'Choose a valid listing status.';
    }

    if (is_numeric($input['building_area_sqm'] ?? null) && (float) $input['building_area_sqm'] <= 0) {
        $errors[] = 'Building area must be greater than zero.';
    }

    if (is_numeric($input['total_area_sqm'] ?? null) && (float) $input['total_area_sqm'] <= 0) {
        $errors[] = 'Total area must be greater than zero.';
    }

    if (
        is_numeric($input['building_area_sqm'] ?? null)
        && is_numeric($input['total_area_sqm'] ?? null)
        && (float) $input['total_area_sqm'] < (float) $input['building_area_sqm']
    ) {
        $errors[] = 'Total area cannot be smaller than the building area.';
    }

    return $errors;
}
