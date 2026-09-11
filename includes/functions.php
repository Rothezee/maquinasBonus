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

function load_prep(): array
{
    if (!is_file(PREP_FILE)) {
        return [];
    }

    $handle = fopen(PREP_FILE, 'rb');
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

function save_prep(array $items): bool
{
    $dir = dirname(PREP_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $json = json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(PREP_FILE, $json, LOCK_EX) !== false;
}

function prep_status_label(string $status): string
{
    return PREP_STATUSES[$status] ?? $status;
}

function sanitize_prep_input(array $input): array
{
    $machineName = clip(trim((string) ($input['machine_name'] ?? '')), 120);
    $clientName = clip(trim((string) ($input['client_name'] ?? '')), 120);
    $clientPhone = clip(trim((string) ($input['client_phone'] ?? '')), 40);
    $location = clip(trim((string) ($input['location'] ?? '')), 200);
    $mapUrl = clip(trim((string) ($input['map_url'] ?? '')), 500);
    $status = trim((string) ($input['status'] ?? 'pendiente'));

    if ($machineName === '') {
        throw new InvalidArgumentException('Indicá qué máquina hay que preparar.');
    }
    if ($clientName === '') {
        throw new InvalidArgumentException('Indicá el nombre del cliente.');
    }
    if ($location === '') {
        throw new InvalidArgumentException('Indicá la ubicación del local.');
    }
    if (!isset(PREP_STATUSES[$status])) {
        $status = 'pendiente';
    }

    // Solo dígitos y + para WhatsApp / teléfono.
    $clientPhone = preg_replace('/[^\d+]/', '', $clientPhone) ?? '';

    if ($mapUrl !== '') {
        if (!preg_match('#^https?://#i', $mapUrl)) {
            $mapUrl = 'https://' . $mapUrl;
        }
        if (!filter_var($mapUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('El link del mapa no es válido. Pegá un link de Google Maps.');
        }
        $scheme = strtolower((string) (parse_url($mapUrl, PHP_URL_SCHEME) ?? ''));
        if ($scheme !== 'http' && $scheme !== 'https') {
            throw new InvalidArgumentException('El link del mapa tiene que empezar con https://');
        }
    }

    return [
        'machine_name' => $machineName,
        'client_name' => $clientName,
        'client_phone' => $clientPhone,
        'location' => $location,
        'map_url' => $mapUrl,
        'status' => $status,
    ];
}

/** Orden: pendientes primero, entregadas al final; dentro del mismo estado, más nuevas arriba. */
function sort_prep(array $items): array
{
    $order = array_flip(array_keys(PREP_STATUSES));
    usort($items, static function (array $a, array $b) use ($order): int {
        $sa = $order[$a['status'] ?? ''] ?? 99;
        $sb = $order[$b['status'] ?? ''] ?? 99;
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
    });
    return $items;
}

function prep_whatsapp_link(array $item): string
{
    $phone = preg_replace('/\D+/', '', (string) ($item['client_phone'] ?? '')) ?? '';
    if ($phone === '') {
        return '';
    }
    // Si vino sin código país, asumir AR móvil típico no — dejar el número tal cual.
    $text = 'Hola ' . ($item['client_name'] ?? '') . ', te escribo por la máquina "' . ($item['machine_name'] ?? '') . '" para el local en ' . ($item['location'] ?? '') . '.';
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
}

function category_label(string $key): string
{
    return CATEGORIES[$key]['label'] ?? $key;
}

function category_full(string $key): string
{
    return CATEGORIES[$key]['full'] ?? $key;
}

/** @return list<string> */
function machine_categories(array $machine): array
{
    $out = [];
    if (!empty($machine['categories']) && is_array($machine['categories'])) {
        foreach ($machine['categories'] as $key) {
            $key = (string) $key;
            if (isset(CATEGORIES[$key])) {
                $out[] = $key;
            }
        }
    } elseif (!empty($machine['category'])) {
        $key = (string) $machine['category'];
        if (isset(CATEGORIES[$key])) {
            $out[] = $key;
        }
    }
    return array_values(array_unique($out));
}

function machine_primary_category(array $machine): string
{
    $cats = machine_categories($machine);
    return $cats[0] ?? 'peluches';
}

/** @return list<string> */
function machine_category_labels(array $machine): array
{
    $labels = [];
    foreach (machine_categories($machine) as $key) {
        $labels[] = category_label($key);
    }
    return $labels;
}

function machine_categories_text(array $machine, string $sep = ' · '): string
{
    $labels = machine_category_labels($machine);
    return $labels ? implode($sep, $labels) : '';
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
        return [default_photo($machine['category'] ?? machine_primary_category($machine))];
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

/**
 * Slides for carousel: photos first, then videos.
 * If there are no photos, videos become the cover.
 * If there is nothing, falls back to the category default image.
 *
 * @return list<array{type:string,src?:string,embed?:string,url?:string,provider?:string}>
 */
function machine_media_slides(array $machine): array
{
    $slides = [];

    foreach (stored_machine_photos($machine) as $src) {
        $slides[] = [
            'type' => 'image',
            'src' => $src,
        ];
    }

    foreach (machine_videos($machine) as $url) {
        $info = video_embed($url);
        $slides[] = [
            'type' => 'video',
            'embed' => (string) ($info['embed'] ?? ''),
            'url' => (string) ($info['url'] ?? $url),
            'provider' => (string) ($info['type'] ?? 'link'),
        ];
    }

    if (!$slides) {
        $slides[] = [
            'type' => 'image',
            'src' => default_photo(machine_primary_category($machine)),
        ];
    }

    return $slides;
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

/**
 * Carga un recurso GD desde disco. Devuelve [resource, mime] o null.
 * @return array{0: mixed, 1: string}|null
 */
function image_create_from_file(string $absPath): ?array
{
    if (!is_file($absPath)) {
        return null;
    }
    $info = @getimagesize($absPath);
    $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
    $img = null;
    switch ($mime) {
        case 'image/jpeg':
            $img = @imagecreatefromjpeg($absPath);
            break;
        case 'image/png':
            $img = @imagecreatefrompng($absPath);
            break;
        case 'image/webp':
            $img = @imagecreatefromwebp($absPath);
            break;
        case 'image/gif':
            $img = @imagecreatefromgif($absPath);
            break;
        default:
            return null;
    }
    if ($img === false || $img === null) {
        return null;
    }

    // Corregir orientación EXIF (celulares).
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($absPath);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        if ($orientation === 3) {
            $img = imagerotate($img, 180, 0);
        } elseif ($orientation === 6) {
            $img = imagerotate($img, -90, 0);
        } elseif ($orientation === 8) {
            $img = imagerotate($img, 90, 0);
        }
    }

    return [$img, $mime];
}

/**
 * Redimensiona (lado largo ≤ IMAGE_MAX_SIDE) y guarda WebP.
 * Devuelve ruta absoluta del .webp o null si falla.
 */
function optimize_image_to_webp(string $sourceAbs, ?string $destAbs = null): ?string
{
    if (!function_exists('imagewebp')) {
        return null;
    }

    $loaded = image_create_from_file($sourceAbs);
    if ($loaded === null) {
        return null;
    }
    [$img, $mime] = $loaded;

    $w = imagesx($img);
    $h = imagesy($img);
    if ($w < 1 || $h < 1) {
        imagedestroy($img);
        return null;
    }

    $max = (int) IMAGE_MAX_SIDE;
    $scale = 1.0;
    if ($w > $max || $h > $max) {
        $scale = min($max / $w, $max / $h);
    }
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));

    if ($nw !== $w || $nh !== $h) {
        $dst = imagecreatetruecolor($nw, $nh);
        if ($dst === false) {
            imagedestroy($img);
            return null;
        }
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $dst;
    } else {
        imagealphablending($img, true);
        imagesavealpha($img, true);
    }

    if ($destAbs === null) {
        $dir = dirname($sourceAbs);
        $base = pathinfo($sourceAbs, PATHINFO_FILENAME);
        $destAbs = $dir . DIRECTORY_SEPARATOR . $base . '.webp';
    }

    $ok = imagewebp($img, $destAbs, (int) IMAGE_WEBP_QUALITY);
    imagedestroy($img);
    if (!$ok || !is_file($destAbs)) {
        return null;
    }

    return $destAbs;
}

/**
 * Optimiza un archivo ya guardado en uploads/ y devuelve la URL relativa (uploads/…).
 * Si ya es un WebP chico, puede reescribirse in-place.
 */
function optimize_stored_upload(string $absPath): ?string
{
    $webpAbs = optimize_image_to_webp($absPath);
    if ($webpAbs === null) {
        return is_file($absPath) ? UPLOAD_URL . basename($absPath) : null;
    }

    $srcReal = realpath($absPath) ?: $absPath;
    $webpReal = realpath($webpAbs) ?: $webpAbs;
    if (strcasecmp($srcReal, $webpReal) !== 0 && is_file($absPath)) {
        @unlink($absPath);
    }

    return UPLOAD_URL . basename($webpAbs);
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

function comodato_whatsapp(): string
{
    $text = 'Hola, quiero info para poner una máquina de ' . SITE_NAME . ' en mi local y cobrar mi porcentaje.';
    return 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($text);
}

function stats(array $machines): array
{
    $rented = 0;
    $sold = 0;
    $available = 0;
    foreach ($machines as $machine) {
        $isRented = !empty($machine['rented']);
        $isSold = !empty($machine['sold']);
        if ($isRented) {
            $rented++;
        }
        if ($isSold) {
            $sold++;
        }
        if (!$isRented && !$isSold) {
            $available++;
        }
    }
    $total = count($machines);
    return [
        'total' => $total,
        'rented' => $rented,
        'sold' => $sold,
        'available' => $available,
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

    // Siempre guardamos WebP optimizado (peso bajo para móvil).
    $name = 'maq_' . bin2hex(random_bytes(8)) . '.webp';
    $dest = UPLOAD_DIR . DIRECTORY_SEPARATOR . $name;

    // Staging con extensión reconocible para GD / EXIF.
    $ext = ALLOWED_IMAGE_TYPES[$mime];
    $staging = UPLOAD_DIR . DIRECTORY_SEPARATOR . 'tmp_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $staging)) {
        throw new RuntimeException('No se pudo guardar la foto.');
    }

    $optimized = optimize_image_to_webp($staging, $dest);
    @unlink($staging);

    if ($optimized === null || !is_file($dest)) {
        throw new RuntimeException('No se pudo optimizar la foto. Probá con JPG o PNG.');
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
    $description = trim((string) ($input['description'] ?? ''));
    $rented = !empty($input['rented']);
    $sold = !empty($input['sold']);
    $videos = sanitize_video_urls($input['videos'] ?? ($input['video_urls'] ?? ''));

    $rawCats = $input['categories'] ?? ($input['category'] ?? []);
    if (is_string($rawCats)) {
        $rawCats = $rawCats === '' ? [] : [$rawCats];
    }
    if (!is_array($rawCats)) {
        $rawCats = [];
    }

    $categories = [];
    foreach ($rawCats as $key) {
        $key = (string) $key;
        if (isset(CATEGORIES[$key])) {
            $categories[] = $key;
        }
    }
    $categories = array_values(array_unique($categories));

    if ($name === '') {
        throw new InvalidArgumentException('El nombre de la máquina es obligatorio.');
    }
    if (!$categories) {
        throw new InvalidArgumentException('Elegí al menos una categoría.');
    }
    if ($description === '') {
        throw new InvalidArgumentException('Agregá una descripción.');
    }

    return [
        'name' => clip($name, 80),
        'categories' => $categories,
        'category' => $categories[0],
        'description' => clip($description, 600),
        'rented' => $rented,
        'sold' => $sold,
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
    $html .= '<img class="brand-mark-img" src="' . e(LOGO_CIRCLE_URL) . '" alt="' . e(SITE_NAME) . ' PLAYPARK">';
    $html .= '<span class="brand-text"><strong>' . e(SITE_NAME) . '</strong><small>' . e($subtitle) . '</small></span>';
    $html .= '</a>';
    return $html;
}
