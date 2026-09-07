<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));
define('DATA_FILE', ROOT_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'machines.json');
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_URL', 'uploads/');

define('SITE_NAME', 'Máquinas Bonus');
define('SITE_BRAND', 'PLAYPARK');
define('SITE_TAGLINE', 'Alquiler de máquinas de juegos');
define('LOGO_URL', 'img/logo-bonus.png');
define('LOGO_CIRCLE_URL', 'assets/img/logo-circle.png');
define('FAVICON_URL', 'assets/img/favicon.png?v=2');
define('SITE_CITY', 'Buenos Aires');
define('WHATSAPP', '2664317619');
define('INSTAGRAM', 'bonus.playpark');
define('FACEBOOK', 'Bonussanluis');
define('TIKTOK', 'bonus.playparkk');
define('ADDRESS', 'Buenos Aires, Argentina');
define('HOURS', 'Lunes a sábado, 10:00 a 22:00 · Domingos, 15:00 a 22:00');
define('ADMIN_PASSWORD', 'bonus2026');

define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
define('MAX_PHOTOS', 8);
define('MAX_VIDEOS', 5);

const CATEGORIES = [
    'peluches' => [
        'label' => 'Peluches',
        'full' => 'Máquinas de peluches',
        'hint' => 'Grúas y claw machines',
    ],
    'tejos' => [
        'label' => 'Tejos',
        'full' => 'Tejos',
        'hint' => 'Mesas de air hockey',
    ],
    'pooles' => [
        'label' => 'Pooles',
        'full' => 'Pooles',
        'hint' => 'Mesas de pool',
    ],
    'kiddies' => [
        'label' => 'Kiddies',
        'full' => 'Kiddies',
        'hint' => 'Juegos para los más chicos',
    ],
    'carreras' => [
        'label' => 'Carreras',
        'full' => 'Máquinas de carreras',
        'hint' => 'Simuladores y driving',
    ],
    'videojuegos' => [
        'label' => 'Videojuegos',
        'full' => 'Videojuegos',
        'hint' => 'Arcade y gabinetes',
    ],
    'clips' => [
        'label' => 'Clips',
        'full' => 'Máquinas de clips',
        'hint' => 'Premios y clips',
    ],
];

const LOCATIONS = [
    [
        'name' => 'Sucursal San Luis',
        'area' => 'San Luis, San Luis',
        'address' => 'Artigas 860',
        'note' => '',
        'map' => 'https://maps.app.goo.gl/SW7ZEszXXEfqaejp6',
    ],
    [
        'name' => 'Sucursal Villa Gesell',
        'area' => 'Villa Gesell',
        'address' => 'Av. 3 975',
        'note' => '',
        'map' => 'https://maps.app.goo.gl/q1kdZZYepN2EWv5r7',
    ],
];

const ALLOWED_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
];
