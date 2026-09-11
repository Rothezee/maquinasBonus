<?php
declare(strict_types=1);

/**
 * One-shot: convierte uploads maq_* a WebP optimizado y actualiza machines.json.
 * Uso: php scripts/optimize-uploads.php
 */

require_once dirname(__DIR__) . '/includes/functions.php';

$uploadsDir = UPLOAD_DIR;
$beforeBytes = 0;
$afterBytes = 0;
$converted = 0;
$skipped = 0;
$failed = 0;
$map = []; // old relative => new relative

$files = glob($uploadsDir . DIRECTORY_SEPARATOR . 'maq_*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
sort($files);

foreach ($files as $abs) {
    $beforeBytes += (int) filesize($abs);
    $base = basename($abs);
    $relOld = UPLOAD_URL . $base;
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));

    $destAbs = $uploadsDir . DIRECTORY_SEPARATOR . pathinfo($abs, PATHINFO_FILENAME) . '.webp';
    $result = optimize_image_to_webp($abs, $destAbs);
    if ($result === null || !is_file($destAbs)) {
        echo "FAIL $base\n";
        $failed++;
        continue;
    }

    $newSize = (int) filesize($destAbs);
    $afterBytes += $newSize;
    $relNew = UPLOAD_URL . basename($destAbs);
    $map[$relOld] = $relNew;

    $same = (realpath($abs) && realpath($destAbs) && strcasecmp((string) realpath($abs), (string) realpath($destAbs)) === 0);
    if (!$same && $ext !== 'webp') {
        @unlink($abs);
    }

    echo sprintf("OK %-40s -> %s (%d KB)\n", $base, basename($destAbs), (int) round($newSize / 1024));
    $converted++;
}

// Recontar before correctamente (ya sumamos al inicio)
// afterBytes solo de webps finales referenciados

$machines = load_machines();
$remap = static function (string $path) use ($map): string {
    $path = str_replace('\\', '/', $path);
    return $map[$path] ?? $path;
};

foreach ($machines as &$machine) {
    if (!empty($machine['photo'])) {
        $machine['photo'] = $remap((string) $machine['photo']);
    }
    if (!empty($machine['photos']) && is_array($machine['photos'])) {
        $machine['photos'] = array_values(array_map($remap, $machine['photos']));
    }
}
unset($machine);

if (!save_machines($machines)) {
    fwrite(STDERR, "ERROR: no se pudo guardar machines.json\n");
    exit(1);
}

// Tamaño final de maq_*.webp
$final = glob($uploadsDir . DIRECTORY_SEPARATOR . 'maq_*.webp') ?: [];
$finalBytes = 0;
foreach ($final as $f) {
    $finalBytes += (int) filesize($f);
}

echo "---\n";
echo "converted=$converted failed=$failed skipped=$skipped\n";
echo 'before≈' . round($beforeBytes / 1048576, 2) . " MB\n";
echo 'after=' . round($finalBytes / 1048576, 2) . " MB (" . count($final) . " webp)\n";
echo 'saved≈' . round(max(0, $beforeBytes - $finalBytes) / 1048576, 2) . " MB\n";
echo "machines.json updated (" . count($machines) . " machines)\n";
