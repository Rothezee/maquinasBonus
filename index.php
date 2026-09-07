<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$machines = load_machines();
$filter = isset($_GET['cat']) && isset(CATEGORIES[$_GET['cat']]) ? $_GET['cat'] : 'all';

$pageTitle = 'Máquinas Bonus PLAYPARK | Tu porcentaje sin invertir un peso';
$pageDescription = 'Poné una grúa de peluches o arcade en tu local sin comprar la máquina. Te llevás del 30% al 50% de las ganancias, directo a tu bolsillo. San Luis y Villa Gesell.';
$pageKeywords = 'Máquinas Bonus, Bonus PLAYPARK, Bonus Play Park, alquiler máquinas de peluches, grúas de peluches, comodato a porcentaje, máquinas arcade, claw machine, videojuegos arcade, máquinas de golpes, máquinas de tickets, tejos, pooles, kiddies, San Luis, Villa Gesell';
$pageOgTitle = 'Máquinas Bonus PLAYPARK | Tu local, tu porcentaje';
$pageOgDescription = 'Sin invertir un peso: instalamos la máquina y vos te llevás del 30% al 50% de las ganancias, directo a tu bolsillo.';
$canonical = rtrim(SITE_URL, '/') . '/';
$ogImage = $canonical . ltrim(LOGO_CIRCLE_URL, '/');
$brandName = SITE_NAME . ' PLAYPARK';
$faqItems = [
    [
        'q' => '¿Cómo funciona el comodato a porcentaje de Máquinas Bonus?',
        'a' => 'Es simple y pensado para vos. Si la máquina da premio (peluches, clips y similares), te llevás el 30% neto de las ganancias — directo a tu bolsillo — y nosotros cubrimos mercadería y envío. Si no da premio (pool, tejo, videojuegos, carreras), repartimos 50/50. En ambos casos instalamos, damos soporte y compartimos reportes.',
    ],
    [
        'q' => '¿Qué máquinas de peluches y arcade ofrecen?',
        'a' => 'Grúas de peluches, claw machines, videojuegos arcade, arcade de pelea, arcade de carrera, simuladores, máquinas de golpes, máquinas de tickets, tejos, pooles, kiddies y máquinas de clips. Mirás el stock en el catálogo y elegís la que mejor te sirva.',
    ],
    [
        'q' => '¿Dónde está Máquinas Bonus PLAYPARK?',
        'a' => 'Estamos en San Luis (Artigas 860) y Villa Gesell (Av. 3 975). Te acompañamos con comodato o alquiler para que tu punto genere sin que tengas que comprar la máquina.',
    ],
    [
        'q' => '¿Alquilan grúas de peluches para locales?',
        'a' => 'Sí. Te instalamos la máquina y la mantenemos. En peluches y máquinas con premio te llevás el 30% de las ganancias; en arcade sin premio, el 50% es para vos.',
    ],
];
$schemaLocations = [];
foreach (LOCATIONS as $place) {
    $schemaLocations[] = [
        '@type' => 'Place',
        'name' => $place['name'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $place['address'],
            'addressLocality' => $place['area'],
            'addressCountry' => 'AR',
        ],
    ];
}
$categoryList = [];
$i = 1;
foreach (CATEGORIES as $key => $cat) {
    $categoryList[] = [
        '@type' => 'ListItem',
        'position' => $i++,
        'name' => $cat['full'],
        'url' => $canonical . '#catalogo',
    ];
}
$faqMainEntity = [];
foreach ($faqItems as $item) {
    $faqMainEntity[] = [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $item['a'],
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="<?php echo e($pageDescription); ?>">
    <meta name="keywords" content="<?php echo e($pageKeywords); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="author" content="<?php echo e($brandName); ?>">
    <meta name="geo.region" content="AR-D">
    <meta name="geo.placename" content="San Luis, Villa Gesell">
    <link rel="canonical" href="<?php echo e($canonical); ?>">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">
    <meta property="og:url" content="<?php echo e($canonical); ?>">
    <meta property="og:title" content="<?php echo e($pageOgTitle); ?>">
    <meta property="og:description" content="<?php echo e($pageOgDescription); ?>">
    <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <meta property="og:site_name" content="<?php echo e($brandName); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($pageOgTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($pageOgDescription); ?>">
    <meta name="twitter:image" content="<?php echo e($ogImage); ?>">

    <link rel="icon" href="<?php echo e(FAVICON_URL); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(LOGO_CIRCLE_URL); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=16">

    <script type="application/ld+json">
    <?php
    echo json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => ['LocalBusiness', 'EntertainmentBusiness'],
                '@id' => $canonical . '#business',
                'name' => $brandName,
                'alternateName' => ['Máquinas Bonus', 'Bonus PLAYPARK', 'Bonus Play Park', 'Bonus'],
                'url' => $canonical,
                'image' => $ogImage,
                'logo' => $ogImage,
                'description' => $pageDescription,
                'telephone' => '+' . WHATSAPP,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => LOCATIONS[0]['address'],
                    'addressLocality' => 'San Luis',
                    'addressRegion' => 'San Luis',
                    'addressCountry' => 'AR',
                ],
                'location' => $schemaLocations,
                'areaServed' => [
                    ['@type' => 'City', 'name' => 'San Luis'],
                    ['@type' => 'City', 'name' => 'Villa Gesell'],
                    ['@type' => 'Country', 'name' => 'Argentina'],
                ],
                'sameAs' => [
                    instagram_url(),
                    facebook_url(),
                    tiktok_url(),
                ],
                'makesOffer' => [
                    [
                        '@type' => 'Offer',
                        'name' => 'Comodato con premio (peluches y similares)',
                        'description' => 'Te llevás el 30% neto de las ganancias, directo a tu bolsillo. Incluye máquina, mercadería, envío, instalación y mantenimiento.',
                    ],
                    [
                        '@type' => 'Offer',
                        'name' => 'Comodato sin premio (pool, tejo, videojuegos, carreras)',
                        'description' => 'El 50% de lo que genera la máquina es para vos. Sin mercadería de premios. Incluye instalación y soporte.',
                    ],
                    [
                        '@type' => 'Offer',
                        'name' => 'Alquiler y colocación de grúas de peluches y arcade',
                        'description' => 'Máquinas de peluches, videojuegos, carreras, arcade de pelea, golpes, tickets, tejos, pooles, kiddies y clips para tu local.',
                    ],
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $canonical . '#website',
                'url' => $canonical,
                'name' => $brandName,
                'alternateName' => ['Máquinas Bonus', 'Bonus Play Park'],
                'description' => $pageDescription,
                'publisher' => ['@id' => $canonical . '#business'],
                'inLanguage' => 'es-AR',
            ],
            [
                '@type' => 'ItemList',
                '@id' => $canonical . '#categorias',
                'name' => 'Categorías de máquinas Bonus PLAYPARK',
                'numberOfItems' => count($categoryList),
                'itemListElement' => $categoryList,
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $canonical . '#faq',
                'mainEntity' => $faqMainEntity,
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
            <a href="#como-trabajamos">Cómo ganás vos</a>
            <a href="#faq">Preguntas</a>
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
            <p class="eyebrow">Máquinas Bonus · PLAYPARK</p>
            <h1>Tu local, tu porcentaje, sin invertir un peso.</h1>
            <p class="hero-highlight">30% a 50% de las ganancias, directo a tu bolsillo</p>
            <p class="lede">Te instalamos grúas de peluches y arcade. Vos te quedás con un porcentaje de las ganancias — <strong>directo a tu bolsillo</strong>. Sin que tengas que comprar la máquina.</p>
            <div class="hero-stats hero-proof" aria-label="Prueba social">
                <div>
                    <em>+8</em>
                    años operando
                </div>
                <div>
                    <em>+10</em>
                    locales ya generan un extra con nosotros
                </div>
            </div>
            <div class="hero-actions">
                <a class="btn btn-gold" href="#catalogo">Ver máquinas</a>
                <a class="btn btn-ghost" href="#como-trabajamos">Cómo ganás vos</a>
            </div>
        </section>

        <section id="catalogo" class="section">
            <div class="section-head">
                <h2>Elegí tu máquina</h2>
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
                    $slides = machine_media_slides($machine);
                    $slideCount = count($slides);
                    $nameLower = function_exists('mb_strtolower')
                        ? mb_strtolower($machine['name'] ?? '')
                        : strtolower($machine['name'] ?? '');
                    $machineCats = machine_categories($machine);
                    $catsAttr = implode(',', $machineCats);
                    $catsText = machine_categories_text($machine);
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
                        data-category="<?php echo e($catsAttr); ?>"
                        data-name="<?php echo e($nameLower); ?>"
                        data-machine="<?php echo e(json_encode([
                            'id' => $machine['id'] ?? '',
                            'name' => $machine['name'] ?? '',
                            'category' => $catsText,
                            'description' => $machine['description'] ?? '',
                            'rented' => $rented,
                            'sold' => $sold,
                            'media' => $slides,
                            'whatsapp' => whatsapp_link($machine),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>"
                    >
                        <div class="photo carousel" data-carousel>
                            <div class="carousel-track">
                                <?php foreach ($slides as $i => $slide): ?>
                                    <?php if (($slide['type'] ?? '') === 'video'): ?>
                                        <div class="carousel-slide is-video" data-slide="<?php echo (int) $i; ?>" <?php echo $i === 0 ? '' : 'hidden'; ?>>
                                            <?php if (!empty($slide['embed'])): ?>
                                                <div class="video-frame">
                                                    <iframe
                                                        <?php if ($i === 0): ?>
                                                            src="<?php echo e($slide['embed']); ?>"
                                                        <?php else: ?>
                                                            data-src="<?php echo e($slide['embed']); ?>"
                                                        <?php endif; ?>
                                                        title="Video de <?php echo e($machine['name'] ?? 'máquina'); ?>"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen
                                                        loading="lazy"
                                                    ></iframe>
                                                </div>
                                            <?php else: ?>
                                                <a class="video-link-slide btn btn-ghost" href="<?php echo e($slide['url'] ?? '#'); ?>" target="_blank" rel="noopener">Ver video</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <img
                                            src="<?php echo e($slide['src'] ?? ''); ?>"
                                            alt="<?php echo e($machine['name'] ?? ''); ?>"
                                            <?php echo $i === 0 ? '' : 'hidden'; ?>
                                            data-slide="<?php echo (int) $i; ?>"
                                            loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                                        >
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($slideCount > 1): ?>
                                <button class="carousel-btn prev" type="button" data-carousel-prev aria-label="Anterior">‹</button>
                                <button class="carousel-btn next" type="button" data-carousel-next aria-label="Siguiente">›</button>
                                <div class="carousel-dots" data-carousel-dots>
                                    <?php foreach ($slides as $i => $slide): ?>
                                        <button
                                            type="button"
                                            class="carousel-dot<?php echo $i === 0 ? ' is-active' : ''; ?><?php echo ($slide['type'] ?? '') === 'video' ? ' is-video' : ''; ?>"
                                            data-carousel-goto="<?php echo (int) $i; ?>"
                                            aria-label="<?php echo ($slide['type'] ?? '') === 'video' ? 'Ir a video ' . (int) ($i + 1) : 'Ir a foto ' . (int) ($i + 1); ?>"
                                        ></button>
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
                            <div class="cat-pills">
                                <?php foreach ($machineCats as $catKey): ?>
                                    <span class="cat-pill"><?php echo e(category_label($catKey)); ?></span>
                                <?php endforeach; ?>
                            </div>
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
                <h2>Cómo ganás vos</h2>
                <p>Comodato claro: vos cobrás tu parte, nosotros nos ocupamos del resto.</p>
            </div>
            <div class="deal-layout">
                <div class="deal-copy">
                    <p class="deal-hook">No hace falta que compres la máquina. La ponemos nosotros y <strong>vos te llevás tu porcentaje</strong>.</p>
                    <p class="lede">Te la instalamos, te explicamos cómo funciona y, si falla, la reparamos sin cargo. Cobramos semanal y te mostramos los números reales, sin filtro.</p>

                    <div class="deal-modes">
                        <article class="deal-mode">
                            <p class="eyebrow">Con premio</p>
                            <h3>30% de las ganancias, para vos</h3>
                            <p>Peluches, clips y máquinas que entregan premio. Vos no comprás mercadería ni pagás el envío: eso lo cubrimos nosotros.</p>
                            <ul class="deal-list">
                                <li><strong>30% neto</strong> directo a tu bolsillo</li>
                                <li>Premios + flete a cargo de Bonus</li>
                            </ul>
                        </article>
                        <article class="deal-mode">
                            <p class="eyebrow">Sin premio</p>
                            <h3>50% de las ganancias, para vos</h3>
                            <p>Pool, tejo, videojuegos, carreras y demás máquinas sin premio físico. Mitad para vos, mitad para Bonus.</p>
                            <ul class="deal-list">
                                <li><strong>50% para vos</strong> · 50% Bonus</li>
                                <li>Ideal para arcade, air hockey, pool y simuladores</li>
                            </ul>
                        </article>
                    </div>

                    <ul class="deal-list deal-list-shared">
                        <li>Máquina + instalación + explicación incluidas</li>
                        <li>Si falla la máquina, la reparamos sin cargo</li>
                        <li>Cobro semanal y reportes que vos también ves</li>
                    </ul>
                    <p class="deal-note">Pensado para tu kiosco, tu local o tu punto con tránsito: vos cobrás, nosotros acompañamos.</p>
                    <a class="btn btn-gold" href="<?php echo e(comodato_whatsapp()); ?>" target="_blank" rel="noopener">Pedí tu máquina sin costo</a>
                </div>
                <aside class="deal-aside" aria-label="Resumen del trato">
                    <p class="eyebrow">En resumen</p>
                    <h3>Lo que te llevás vos</h3>
                    <ul class="deal-split">
                        <li><span>30% para vos</span> con premio · peluches, clips…</li>
                        <li><span>50% para vos</span> sin premio · pool, tejo, arcade…</li>
                        <li><span>Todo incluido</span> instalación, soporte y reportes</li>
                    </ul>
                    <p>Contanos qué necesitás y te armamos el trato a tu medida.</p>
                </aside>
            </div>
        </section>

        <section id="categorias" class="section">
            <div class="section-head">
                <h2>Tipos de máquina</h2>
                <p>Tocá una categoría para filtrar el catálogo.</p>
            </div>
            <div class="category-grid">
                <?php foreach (CATEGORIES as $key => $cat): ?>
                    <button class="cat-card<?php echo $filter === $key ? ' is-active' : ''; ?>" type="button" data-filter="<?php echo e($key); ?>">
                        <img src="assets/img/defaults/<?php echo e($key); ?>.svg" alt="<?php echo e($cat['full']); ?> — Máquinas Bonus PLAYPARK" loading="lazy">
                        <strong><?php echo e($cat['full']); ?></strong>
                        <span><?php echo e($cat['hint']); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="seo-machines">
                <h3>Máquinas arcade y de entretenimiento</h3>
                <p>En <strong>Máquinas Bonus PLAYPARK</strong> te ofrecemos <strong>grúas de peluches</strong>, claw machines, <strong>videojuegos arcade</strong>, pelea, carrera y simuladores, <strong>máquinas de golpes</strong>, <strong>máquinas de tickets</strong>, tejos, pooles, kiddies y clips. Elegís la tuya en el catálogo y nosotros nos ocupamos de ponerla a trabajar para vos.</p>
            </div>
        </section>

        <section id="faq" class="section faq-section">
            <div class="section-head">
                <h2>Preguntas frecuentes</h2>
                <p>Lo que más nos preguntan antes de sumar una máquina.</p>
            </div>
            <div class="faq-list">
                <?php foreach ($faqItems as $item): ?>
                    <article class="faq-item">
                        <h3><?php echo e($item['q']); ?></h3>
                        <p><?php echo e($item['a']); ?></p>
                    </article>
                <?php endforeach; ?>
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
            <img src="<?php echo e(LOGO_CIRCLE_URL); ?>" alt="<?php echo e(SITE_NAME); ?> PLAYPARK">
            <p><strong><?php echo e(SITE_NAME); ?> PLAYPARK</strong><br><?php echo e(SITE_TAGLINE); ?><br><?php echo e(ADDRESS); ?></p>
        </div>
        <div class="footer-links">
            <a href="#catalogo">Catálogo</a>
            <a href="#como-trabajamos">Cómo ganás vos</a>
            <a href="#faq">Preguntas</a>
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
                    <div class="cat-pills" id="viewer-category"></div>
                    <h2 id="viewer-title"></h2>
                    <p id="viewer-status" class="viewer-status"></p>
                    <p id="viewer-description"></p>
                    <p class="viewer-deal"><a href="#como-trabajamos" id="viewer-deal-link">Cómo ganás vos con el comodato</a></p>
                    <div class="viewer-actions">
                        <a class="btn btn-gold" id="viewer-whatsapp" href="#" target="_blank" rel="noopener">Consultar por WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=12"></script>
</body>
</html>
