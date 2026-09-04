<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clip(string $value, int $max): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }
    return substr($value, 0, $max);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function load_machines(): array
{
    if (!is_file(DATA_FILE)) {
        return [];
    }

    $handle = fopen(DATA_FILE, 'rb');
    if ($handle === false) {
        return [];
    }

    flock($handle, LOCK_SH);
    $raw = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function save_machines(array $machines): bool
{
    $dir = dirname(DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $json = json_encode(array_values($machines), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(DATA_FILE, $json, LOCK_EX) !== false;
}

function find_machine(string $id): ?array
{
    foreach (load_machines() as $machine) {
        if (($machine['id'] ?? '') === $id) {
            return $machine;
        }
    }
    return null;
}

function category_label(string $key): string
{
    return CATEGORIES[$key]['label'] ?? $key;
}

function category_full(string $key): string
{
    return CATEGORIES[$key]['full'] ?? $key;
}

function default_photo(string $category): string
{
    $file = 'assets/img/defaults/' . $category . '.svg';
    if (is_file(ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file))) {
        return $file;
    }
    return 'assets/img/defaults/peluches.svg';
}

function machine_photo(array $machine): string
{
    $photo = $machine['photo'] ?? '';
    if ($photo !== '' && is_file(ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $photo))) {
        return str_replace('\\', '/', $photo);
    }
    return default_photo($machine['category'] ?? 'peluches');
}

function whatsapp_link(?array $machine = null): string
{
    $text = 'Hola, quiero consultar por el alquiler de máquinas de ' . SITE_NAME . '.';
    if ($machine) {
        $text = 'Hola, quiero consultar disponibilidad de "' . ($machine['name'] ?? 'una máquina') . '" en ' . SITE_NAME . '.';
    }
    return 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($text);
}

function instagram_url(): string
{
    return 'https://instagram.com/' . INSTAGRAM;
}

function facebook_url(): string
{
    return 'https://facebook.com/' . FACEBOOK;
}

function tiktok_url(): string
{
    return 'https://www.tiktok.com/@' . TIKTOK;
}

function franchise_whatsapp(): string
{
    $text = 'Hola, quiero información para abrir una franquicia de ' . SITE_NAME . ' PLAYPARK.';
    return 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($text);
}

function stats(array $machines): array
{
    $rented = 0;
    foreach ($machines as $machine) {
        if (!empty($machine['rented'])) {
            $rented++;
        }
    }
    $total = count($machines);
    return [
        'total' => $total,
        'rented' => $rented,
        'available' => $total - $rented,
        'categories' => count(CATEGORIES),
    ];
}

function handle_upload(?array $file, ?string $previous = null): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $previous;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la foto. Probá de nuevo.');
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('La foto supera los 5 MB.');
    }

    $info = @getimagesize($file['tmp_name']);
    $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
    if (!isset(ALLOWED_IMAGE_TYPES[$mime])) {
        throw new RuntimeException('Usá una imagen JPG, PNG, WEBP o GIF.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $name = 'maq_' . bin2hex(random_bytes(8)) . '.' . ALLOWED_IMAGE_TYPES[$mime];
    $dest = UPLOAD_DIR . DIRECTORY_SEPARATOR . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('No se pudo guardar la foto.');
    }

    if ($previous && strpos($previous, 'uploads/') === 0) {
        $old = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $previous);
        if (is_file($old)) {
            @unlink($old);
        }
    }

    return UPLOAD_URL . $name;
}

function sanitize_machine_input(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $category = (string) ($input['category'] ?? '');
    $description = trim((string) ($input['description'] ?? ''));
    $rented = !empty($input['rented']);

    if ($name === '') {
        throw new InvalidArgumentException('El nombre de la máquina es obligatorio.');
    }
    if (!isset(CATEGORIES[$category])) {
        throw new InvalidArgumentException('Elegí una categoría válida.');
    }
    if ($description === '') {
        throw new InvalidArgumentException('Agregá una descripción.');
    }

    return [
        'name' => clip($name, 80),
        'category' => $category,
        'description' => clip($description, 600),
        'rented' => $rented,
    ];
}

function require_login(): void
{
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}

function require_api_login(): void
{
    if (empty($_SESSION['admin'])) {
        json_response(['ok' => false, 'error' => 'Tenés que ingresar al panel.'], 401);
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin']);
}

function logo_url(): string
{
    return LOGO_URL;
}

function brand_html(string $variant = 'nav', string $subtitle = ''): string
{
    if ($subtitle === '') {
        $subtitle = SITE_BRAND;
    }
    $html = '<a class="brand brand-' . e($variant) . '" href="index.php">';
    $html .= '<img class="brand-mark-img" src="' . e(LOGO_CIRCLE_URL) . '" alt="' . e(SITE_NAME) . '">';
    $html .= '<span class="brand-text"><strong>' . e(SITE_NAME) . '</strong><small>' . e($subtitle) . '</small></span>';
    $html .= '</a>';
    return $html;
}
