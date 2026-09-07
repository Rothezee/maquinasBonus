<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$machines = load_machines();
$filter = isset($_GET['cat']) && isset(CATEGORIES[$_GET['cat']]) ? $_GET['cat'] : 'all';

$pageTitle = SITE_NAME . ' PLAYPARK | Arcade, grúas de peluches y máquinas de juegos';
$pageDescription = 'Alquiler y comodato de máquinas arcade: grúas de peluches, videojuegos, carreras, arcade de pelea, box punch, tejos, pooles y kiddies. San Luis y Villa Gesell. Tu local se lleva el 30% neto.';
$pageKeywords = 'arcade, máquinas de peluches, grúas de peluches, peluches, claw machine, máquinas de juegos, videojuegos arcade, arcade de carrera, videojuegos de carreras, arcade de pelea, máquina de box, boxpunch, box punch, tejos, air hockey, pooles, kiddies, alquiler de máquinas, comodato a porcentaje, San Luis, Villa Gesell, Bonus PLAYPARK';
$canonical = rtrim(SITE_URL, '/') . '/';
$ogImage = $canonical . ltrim(LOGO_CIRCLE_URL, '/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="<?php echo e($pageDescription); ?>">
    <meta name="keywords" content="<?php echo e($pageKeywords); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="author" content="<?php echo e(SITE_NAME); ?> PLAYPARK">
    <meta name="geo.region" content="AR">
    <meta name="geo.placename" content="San Luis, Villa Gesell">
    <link rel="canonical" href="<?php echo e($canonical); ?>">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">
    <meta property="og:url" content="<?php echo e($canonical); ?>">
    <meta property="og:title" content="<?php echo e($pageTitle); ?>">
    <meta property="og:description" content="<?php echo e($pageDescription); ?>">
    <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <meta property="og:site_name" content="<?php echo e(SITE_NAME); ?> PLAYPARK">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo e($ogImage); ?>">

    <link rel="icon" href="<?php echo e(FAVICON_URL); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(LOGO_CIRCLE_URL); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=10">

    <script type="application/ld+json">
    <?php
    echo json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'LocalBusiness',
                '@id' => $canonical . '#business',
                'name' => SITE_NAME . ' PLAYPARK',
                'url' => $canonical,
                'image' => $ogImage,
                'description' => $pageDescription,
                'telephone' => '+' . WHATSAPP,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'AR',
                    'addressLocality' => 'San Luis',
                ],
                'areaServed' => ['San Luis', 'Villa Gesell', 'Argentina'],
                'sameAs' => [
                    instagram_url(),
                    facebook_url(),
                    tiktok_url(),
                ],
                'makesOffer' => [
                    [
                        '@type' => 'Offer',
                        'name' => 'Comodato de máquinas arcade a porcentaje',
                        'description' => 'El local se lleva el 30% neto. Incluye máquina, mercadería, envío, instalación y mantenimiento.',
                    ],
                    [
                        '@type' => 'Offer',
                        'name' => 'Alquiler y colocación de grúas de peluches y arcade',
                        'description' => 'Máquinas de peluches, videojuegos, carreras, arcade de pelea, box punch, tejos, pooles y kiddies.',
                    ],
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $canonical . '#website',
                'url' => $canonical,
                'name' => SITE_NAME . ' PLAYPARK',
                'description' => $pageDescription,
                'publisher' => ['@id' => $canonical . '#business'],
                'inLanguage' => 'es-AR',
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $canonical . '#faq',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => '¿Cómo funciona el comodato a porcentaje?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Tu local se lleva el 30% neto de lo que genera la máquina. Bonus se encarga de la máquina, los peluches u otros premios, el envío, la instalación, la reparación de fallas propias y los reportes.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => '¿Qué máquinas arcade ofrecen?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Grúas de peluches, claw machines, videojuegos arcade, arcade de pelea, arcade de carrera, simuladores, máquina de box / box punch, tejos, pooles, kiddies y máquinas de clips.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => '¿Dónde están ubicados?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Sucursales en San Luis (Artigas 860) y Villa Gesell (Av. 3 975). Trabajamos comodato y alquiler de máquinas para locales.',
                        ],
                    ],
                ],
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    ?>
    </script>
</head>
<body>
    <div class="noise" aria-hidden="true"></div>
    <div class="site-header">
        <header class="topbar">
            <?php echo brand_html('nav'); ?>
            <div class="topbar-actions">
                <a class="btn btn-whatsapp" href="<?php echo e(whatsapp_link()); ?>" target="_blank" rel="noopener">Consultar</a>
                <button
                    class="nav-toggle"
                    type="button"
                    id="nav-toggle"
                    aria-expanded="false"
                    aria-controls="site-nav"
                >
                    Menú
                </button>
            </div>
        </header>
        <nav class="topnav" id="site-nav" aria-label="Principal">
            <a href="#catalogo">Catálogo</a>
            <a href="#como-trabajamos">Cómo trabajamos</a>
            <a href="#ubicaciones">Ubicaciones</a>
            <a href="#franquicias">Franquicias</a>
            <a class="btn btn-whatsapp topnav-whatsapp-mobile" href="<?php echo e(whatsapp_link()); ?>" target="_blank" rel="noopener">Consultar por WhatsApp</a>
            <?php if (is_logged_in()): ?>
                <a class="btn btn-ghost" href="admin.php">Panel</a>
            <?php else: ?>
                <a class="btn btn-ghost" href="login.php">Ingresar</a>
            <?php endif; ?>
        </nav>
    </div>

    <main>
        <section class="hero">
            <p class="eyebrow">Arcade · Alquiler · Comodato</p>
            <h1>Máquinas arcade listas para tu local</h1>
            <p class="lede">Grúas de peluches, videojuegos, carreras, arcade de pelea, box punch, tejos, pooles y kiddies. Si una máquina está alquilada o vendida, aparece con aviso en el catálogo.</p>
        </section>

        <section id="categorias" class="section">
            <div class="section-head">
                <h2>Qué ofrecemos</h2>
                <p>Máquinas de juegos y arcade para locales: elegí el tipo y mirá el stock actual.</p>
            </div>
            <div class="category-grid">
                <?php foreach (CATEGORIES as $key => $cat): ?>
                    <button class="cat-card<?php echo $filter === $key ? ' is-active' : ''; ?>" type="button" data-filter="<?php echo e($key); ?>">
                        <img src="assets/img/defaults/<?php echo e($key); ?>.svg" alt="">
                        <strong><?php echo e($cat['full']); ?></strong>
                        <span><?php echo e($cat['hint']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="catalogo" class="section">
            <div class="section-head">
                <h2>Catálogo de máquinas</h2>
                <div class="toolbar">
                    <label class="search">
                        <span class="sr-only">Buscar máquina</span>
                        <input type="search" id="search" placeholder="Buscar por nombre...">
                    </label>
                    <div class="chips" id="chips">
                        <button type="button" class="chip<?php echo $filter === 'all' ? ' is-active' : ''; ?>" data-filter="all">Todas</button>
                        <?php foreach (CATEGORIES as $key => $cat): ?>
                            <button type="button" class="chip<?php echo $filter === $key ? ' is-active' : ''; ?>" data-filter="<?php echo e($key); ?>"><?php echo e($cat['label']); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="machine-grid" id="machine-grid">
                <?php foreach ($machines as $machine): ?>
                    <?php
                    $rented = !empty($machine['rented']);
                    $sold = !empty($machine['sold']);
                    $unavailable = $rented || $sold;
                    $photos = machine_photos($machine);
                    $videos = machine_videos($machine);
                    $nameLower = function_exists('mb_strtolower')
                        ? mb_strtolower($machine['name'] ?? '')
                        : strtolower($machine['name'] ?? '');
                    $cardClass = 'machine-card';
                    if ($rented) {
                        $cardClass .= ' is-rented';
                    }
                    if ($sold) {
                        $cardClass .= ' is-sold';
                    }
                    ?>
                    <article
                        class="<?php echo $cardClass; ?>"
                        data-category="<?php echo e($machine['category'] ?? ''); ?>"
                        data-name="<?php echo e($nameLower); ?>"
                        data-machine="<?php echo e(json_encode([
                            'id' => $machine['id'] ?? '',
                            'name' => $machine['name'] ?? '',
                            'category' => category_label($machine['category'] ?? ''),
                            'description' => $machine['description'] ?? '',
                            'rented' => $rented,
                            'sold' => $sold,
                            'photos' => $photos,
                            'videos' => array_map('video_embed', $videos),
                            'whatsapp' => whatsapp_link($machine),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>"
                    >
                        <div class="photo carousel" data-carousel>
                            <div class="carousel-track">
                                <?php foreach ($photos as $i => $src): ?>
                                    <img src="<?php echo e($src); ?>" alt="<?php echo e($machine['name'] ?? ''); ?>" <?php echo $i === 0 ? '' : 'hidden'; ?> data-slide="<?php echo (int) $i; ?>">
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($photos) > 1): ?>
                                <button class="carousel-btn prev" type="button" data-carousel-prev aria-label="Foto anterior">‹</button>
                                <button class="carousel-btn next" type="button" data-carousel-next aria-label="Foto siguiente">›</button>
                                <div class="carousel-dots" data-carousel-dots>
                                    <?php foreach ($photos as $i => $_): ?>
                                        <button type="button" class="carousel-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" data-carousel-goto="<?php echo (int) $i; ?>" aria-label="Ir a foto <?php echo (int) ($i + 1); ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($rented): ?>
                                <div class="rented-banner" aria-label="Alquilada">
                                    <span>Alquilada</span>
                                </div>
                            <?php elseif ($sold): ?>
                                <div class="sold-banner" aria-label="Vendida">
                                    <span>Vendida</span>
                                </div>
                            <?php else: ?>
                                <span class="status-pill available">Disponible</span>
                            <?php endif; ?>
                            <span class="cat-pill"><?php echo e(category_label($machine['category'] ?? '')); ?></span>
                        </div>
                        <div class="body">
                            <h3><?php echo e($machine['name'] ?? ''); ?></h3>
                            <p><?php echo e($machine['description'] ?? ''); ?></p>
                            <div class="card-actions">
                                <button class="btn btn-ghost js-open-machine" type="button">Ver más</button>
                                <?php if ($unavailable): ?>
                                    <span class="btn btn-disabled">No disponible</span>
                                <?php else: ?>
                                    <a class="btn btn-gold" href="<?php echo e(whatsapp_link($machine)); ?>" target="_blank" rel="noopener">Consultar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="empty-state" id="empty-state" hidden>No hay máquinas en esta categoría por ahora.</p>
        </section>

        <section id="como-trabajamos" class="section deal-section">
            <div class="section-head">
                <h2>Cómo trabajamos</h2>
                <p>Comodato a porcentaje, sin letra chica escondida.</p>
            </div>
            <div class="deal-layout">
                <div class="deal-copy">
                    <p class="deal-hook">Tu local se lleva el <strong>30% neto</strong> de lo que genera la máquina. Sin comprar mercadería ni pagar el envío.</p>
                    <p class="lede">Ponemos la máquina, la instalamos y te explicamos cómo opera. Los peluches u otros premios van por nuestra cuenta, el flete también, y si falla la máquina la reparamos sin cargo. Cobramos de forma semanal y compartimos reportes reales, sin filtro.</p>
                    <ul class="deal-list">
                        <li><strong>30% neto para el local</strong> sobre lo que genera la máquina</li>
                        <li>Máquina + instalación + explicación incluidas</li>
                        <li>Mercadería y envío a cargo de Bonus</li>
                        <li>Reparación de fallas propias de la máquina sin cargo</li>
                        <li>Cobro semanal y reportes compartidos en tiempo real</li>
                    </ul>
                    <p class="deal-note">Del resto nos hacemos cargo de premios, logística y operación: vos y nosotros ganamos en un rango similar. Ideal para kioscos, locales y puntos con tránsito.</p>
                    <a class="btn btn-gold" href="<?php echo e(comodato_whatsapp()); ?>" target="_blank" rel="noopener">Consultar comodato por WhatsApp</a>
                </div>
                <aside class="deal-aside" aria-label="Resumen del trato">
                    <p class="eyebrow">El trato en claro</p>
                    <h3>De cada $100 de la máquina</h3>
                    <ul class="deal-split">
                        <li><span>~$30</span> para tu local</li>
                        <li><span>Premios + envío</span> los cubrimos nosotros</li>
                        <li><span>Operación Bonus</span> máquina, soporte y reportes</li>
                    </ul>
                    <p>Sin costo inicial de mercadería. Transparencia que en el rubro casi nadie publica.</p>
                </aside>
            </div>
            <div class="seo-machines">
                <h3>Máquinas arcade y de entretenimiento</h3>
                <p>Trabajamos con <strong>grúas de peluches</strong> y claw machines, <strong>videojuegos arcade</strong>, <strong>arcade de pelea</strong>, <strong>arcade de carrera</strong> y simuladores, <strong>máquina de box / box punch</strong>, tejos (air hockey), pooles, kiddies y máquinas de clips. Alquiler, comodato a porcentaje y stock disponible en el catálogo.</p>
            </div>
        </section>

        <section id="ubicaciones" class="section">
            <div class="section-head">
                <h2>Ubicaciones</h2>
                <p><?php echo e(HOURS); ?></p>
            </div>
            <div class="location-grid">
                <?php foreach (LOCATIONS as $place): ?>
                    <article class="location-card">
                        <p class="eyebrow"><?php echo e($place['area']); ?></p>
                        <h3><?php echo e($place['name']); ?></h3>
                        <p><?php echo e($place['address']); ?></p>
                        <p><?php echo e($place['note']); ?></p>
                        <?php if (!empty($place['map'])): ?>
                            <a class="btn btn-ghost" href="<?php echo e($place['map']); ?>" target="_blank" rel="noopener">Ver en el mapa</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="franquicias" class="section">
            <div class="section-head">
                <h2>Franquicias</h2>
                <p>Un local Bonus! PLAYPARK, con marca, máquinas y operación simple.</p>
            </div>
            <div class="franchise-layout">
                <div>
                    <p class="lede">El modelo ya lo entiende la gente: entran, juegan y vuelven. Nosotros armamos el local, las máquinas y el soporte para que puedas operar. También podés empezar con <a href="#como-trabajamos">comodato a porcentaje</a> en un punto existente.</p>
                    <ul class="franchise-list">
                        <li>Marca y derecho de uso Bonus! PLAYPARK</li>
                        <li>Asesoramiento de layout del local</li>
                        <li>Provisión e instalación de máquinas arcade y grúas de peluches</li>
                        <li>Capacitación y puesta en marcha</li>
                        <li>Soporte de mantenimiento</li>
                    </ul>
                    <a class="btn btn-gold" href="<?php echo e(franchise_whatsapp()); ?>" target="_blank" rel="noopener">Quiero una franquicia</a>
                </div>
                <aside class="franchise-aside">
                    <h3>Por qué funciona</h3>
                    <p>Alta rotación, operación simple y un formato que ya se busca en la calle. No hace falta experiencia previa en entretenimiento.</p>
                </aside>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-brand">
            <img src="<?php echo e(LOGO_CIRCLE_URL); ?>" alt="<?php echo e(SITE_NAME); ?>">
            <p><strong><?php echo e(SITE_NAME); ?></strong><br><?php echo e(SITE_TAGLINE); ?><br><?php echo e(ADDRESS); ?></p>
        </div>
        <div class="footer-links">
            <a href="#catalogo">Catálogo</a>
            <a href="#como-trabajamos">Cómo trabajamos</a>
            <a href="#ubicaciones">Ubicaciones</a>
            <a href="#franquicias">Franquicias</a>
        </div>
        <div class="social-row">
            <a class="social-icon" href="<?php echo e(instagram_url()); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                <img src="assets/img/social/instagram.svg" alt="">
            </a>
            <a class="social-icon" href="<?php echo e(tiktok_url()); ?>" target="_blank" rel="noopener" aria-label="TikTok">
                <img src="assets/img/social/tiktok.svg" alt="">
            </a>
            <a class="social-icon" href="<?php echo e(facebook_url()); ?>" target="_blank" rel="noopener" aria-label="Facebook">
                <img src="assets/img/social/facebook.svg" alt="">
            </a>
            <a class="social-icon social-icon-whatsapp" href="<?php echo e(whatsapp_link()); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                <img src="assets/img/social/whatsapp.svg" alt="">
            </a>
        </div>
    </footer>
    <p class="legal">© <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?> PLAYPARK. Todos los derechos reservados.</p>

    <div class="modal viewer-modal" id="machine-viewer" hidden>
        <div class="modal-card viewer-card">
            <button class="modal-close" type="button" id="close-viewer" aria-label="Cerrar">×</button>
            <div class="viewer-layout">
                <div class="photo carousel viewer-carousel" data-viewer-carousel>
                    <div class="carousel-track" id="viewer-track"></div>
                    <button class="carousel-btn prev" type="button" data-viewer-prev aria-label="Foto anterior">‹</button>
                    <button class="carousel-btn next" type="button" data-viewer-next aria-label="Foto siguiente">›</button>
                    <div class="carousel-dots" id="viewer-dots"></div>
                </div>
                <div class="viewer-body">
                    <p class="cat-pill" id="viewer-category"></p>
                    <h2 id="viewer-title"></h2>
                    <p id="viewer-status" class="viewer-status"></p>
                    <p id="viewer-description"></p>
                    <p class="viewer-deal"><a href="#como-trabajamos" id="viewer-deal-link">Modelo comodato a % — ver cómo trabajamos</a></p>
                    <div id="viewer-videos" class="viewer-videos" hidden></div>
                    <div class="viewer-actions">
                        <a class="btn btn-gold" id="viewer-whatsapp" href="#" target="_blank" rel="noopener">Consultar por WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=8"></script>
</body>
</html>
