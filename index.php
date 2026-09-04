<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$machines = load_machines();
$filter = isset($_GET['cat']) && isset(CATEGORIES[$_GET['cat']]) ? $_GET['cat'] : 'all';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(SITE_NAME); ?> — Alquiler de máquinas de juegos</title>
    <meta name="description" content="Alquiler de máquinas de peluches, tejos, pooles, kiddies, carreras, videojuegos y clips.">
    <link rel="icon" href="<?php echo e(FAVICON_URL); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(LOGO_CIRCLE_URL); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="noise" aria-hidden="true"></div>
    <header class="topbar">
        <?php echo brand_html('nav'); ?>
        <nav class="topnav">
            <a href="#catalogo">Catálogo</a>
            <a href="#ubicaciones">Ubicaciones</a>
            <a href="#franquicias">Franquicias</a>
            <a href="#categorias">Categorías</a>
            <a class="btn btn-whatsapp" href="<?php echo e(whatsapp_link()); ?>" target="_blank" rel="noopener">Consultar</a>
            <?php if (is_logged_in()): ?>
                <a class="btn btn-ghost" href="admin.php">Panel</a>
            <?php else: ?>
                <a class="btn btn-ghost" href="login.php">Ingresar</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section class="hero">
            <p class="eyebrow">Salón de juegos · Alquiler</p>
            <h1>Máquinas listas para tu local</h1>
            <p class="lede">Peluches, tejos, pooles, kiddies, carreras, videojuegos y clips. Si una máquina está alquilada, aparece con aviso en el catálogo.</p>
        </section>

        <section id="categorias" class="section">
            <div class="section-head">
                <h2>Qué ofrecemos</h2>
                <p>Elegí el tipo de máquina y mirá el stock actual.</p>
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
                <h2>Catálogo</h2>
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
                    <?php $rented = !empty($machine['rented']); ?>
                    <article
                        class="machine-card<?php echo $rented ? ' is-rented' : ''; ?>"
                        data-category="<?php echo e($machine['category'] ?? ''); ?>"
                        data-name="<?php echo e(mb_strtolower($machine['name'] ?? '')); ?>"
                    >
                        <div class="photo">
                            <img src="<?php echo e(machine_photo($machine)); ?>" alt="<?php echo e($machine['name'] ?? ''); ?>">
                            <?php if ($rented): ?>
                                <div class="rented-banner" aria-label="Alquilada">
                                    <span>Alquilada</span>
                                </div>
                            <?php else: ?>
                                <span class="status-pill available">Disponible</span>
                            <?php endif; ?>
                            <span class="cat-pill"><?php echo e(category_label($machine['category'] ?? '')); ?></span>
                        </div>
                        <div class="body">
                            <h3><?php echo e($machine['name'] ?? ''); ?></h3>
                            <p><?php echo e($machine['description'] ?? ''); ?></p>
                            <?php if ($rented): ?>
                                <span class="btn btn-disabled">No disponible</span>
                            <?php else: ?>
                                <a class="btn btn-gold" href="<?php echo e(whatsapp_link($machine)); ?>" target="_blank" rel="noopener">Consultar por WhatsApp</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="empty-state" id="empty-state" hidden>No hay máquinas en esta categoría por ahora.</p>
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
                    <p class="lede">El modelo ya lo entiende la gente: entran, juegan y vuelven. Nosotros armamos el local, las máquinas y el soporte para que puedas operar.</p>
                    <ul class="franchise-list">
                        <li>Marca y derecho de uso Bonus! PLAYPARK</li>
                        <li>Asesoramiento de layout del local</li>
                        <li>Provisión e instalación de máquinas</li>
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

    <script src="assets/js/app.js"></script>
</body>
</html>
