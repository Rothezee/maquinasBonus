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

function photo_exists(string $photo): bool
{
    if ($photo === '') {
        return false;
    }
    $path = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $photo);
    return is_file($path);
}

function stored_machine_photos(array $machine): array
{
    $raw = [];
    if (!empty($machine['photos']) && is_array($machine['photos'])) {
        foreach ($machine['photos'] as $photo) {
            $photo = str_replace('\\', '/', trim((string) $photo));
            if ($photo !== '') {
                $raw[] = $photo;
            }
        }
    } elseif (!empty($machine['photo'])) {
        $raw[] = str_replace('\\', '/', (string) $machine['photo']);
    }

    $photos = [];
    foreach ($raw as $photo) {
        if (strpos($photo, 'uploads/') === 0 && photo_exists($photo)) {
            $photos[] = $photo;
        }
    }

    return array_values(array_unique($photos));
}

function machine_photos(array $machine): array
{
    $photos = stored_machine_photos($machine);
    if (!$photos) {
        return [default_photo($machine['category'] ?? 'peluches')];
    }
    return $photos;
}

function machine_photo(array $machine): string
{
    return machine_photos($machine)[0];
}

function machine_videos(array $machine): array
{
    if (empty($machine['videos']) || !is_array($machine['videos'])) {
        return [];
    }
    $out = [];
    foreach ($machine['videos'] as $video) {
        $video = trim((string) $video);
        if ($video !== '') {
            $out[] = $video;
        }
    }
    return array_values(array_unique($out));
}

function sanitize_video_urls($raw): array
{
    if (is_string($raw)) {
        $raw = preg_split('/[\r\n,]+/', $raw) ?: [];
    }
    if (!is_array($raw)) {
        return [];
    }

    $videos = [];
    foreach ($raw as $item) {
        $url = trim((string) $item);
        if ($url === '') {
            continue;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Revisá que los links de video sean URLs válidas.');
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Los videos tienen que empezar con http:// o https://.');
        }
        $videos[] = clip($url, 400);
        if (count($videos) >= MAX_VIDEOS) {
            break;
        }
    }
    return array_values(array_unique($videos));
}

function video_embed(string $url): array
{
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $path = (string) parse_url($url, PHP_URL_PATH);
    $query = [];
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    if (strpos($host, 'youtu.be') !== false) {
        $id = trim($path, '/');
        if ($id !== '') {
            return ['type' => 'youtube', 'embed' => 'https://www.youtube.com/embed/' . rawurlencode($id), 'url' => $url];
        }
    }

    if (strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false) {
        if (!empty($query['v'])) {
            return ['type' => 'youtube', 'embed' => 'https://www.youtube.com/embed/' . rawurlencode($query['v']), 'url' => $url];
        }
        if (preg_match('#/(embed|shorts)/([^/?]+)#', $path, $m)) {
            return ['type' => 'youtube', 'embed' => 'https://www.youtube.com/embed/' . rawurlencode($m[2]), 'url' => $url];
        }
    }

    if (strpos($host, 'tiktok.com') !== false && preg_match('#/video/(\d+)#', $path, $m)) {
        return ['type' => 'tiktok', 'embed' => 'https://www.tiktok.com/embed/v2/' . $m[1], 'url' => $url];
    }

    if (strpos($host, 'drive.google.com') !== false && preg_match('#/file/d/([^/]+)#', $path, $m)) {
        return ['type' => 'drive', 'embed' => 'https://drive.google.com/file/d/' . rawurlencode($m[1]) . '/preview', 'url' => $url];
    }

    return ['type' => 'link', 'embed' => '', 'url' => $url];
}

function delete_upload(?string $photo): void
{
    if (!$photo || strpos($photo, 'uploads/') !== 0) {
        return;
    }
    $old = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $photo);
    if (is_file($old)) {
        @unlink($old);
    }
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

    if ($previous) {
        delete_upload($previous);
    }

    return UPLOAD_URL . $name;
}

function normalize_files_array(?array $files): array
{
    if (!$files || !isset($files['name'])) {
        return [];
    }

    if (!is_array($files['name'])) {
        return [$files];
    }

    $out = [];
    foreach ($files['name'] as $i => $name) {
        $out[] = [
            'name' => $name,
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
    }
    return $out;
}

function handle_uploads(?array $files, array $keep = []): array
{
    $keep = array_values(array_filter(array_map(static function ($photo) {
        $photo = str_replace('\\', '/', trim((string) $photo));
        return photo_exists($photo) ? $photo : null;
    }, $keep)));

    $photos = $keep;
    foreach (normalize_files_array($files) as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (count($photos) >= MAX_PHOTOS) {
            throw new InvalidArgumentException('Podés subir hasta ' . MAX_PHOTOS . ' fotos por máquina.');
        }
        $uploaded = handle_upload($file, null);
        if ($uploaded) {
            $photos[] = $uploaded;
        }
    }

    return array_values(array_unique($photos));
}

function sanitize_machine_input(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $category = (string) ($input['category'] ?? '');
    $description = trim((string) ($input['description'] ?? ''));
    $rented = !empty($input['rented']);
    $videos = sanitize_video_urls($input['videos'] ?? ($input['video_urls'] ?? ''));

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
        'videos' => $videos,
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
