<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_login();

$machines = load_machines();
$stats = stats($machines);
$prepItems = sort_prep(load_prep());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel — <?php echo e(SITE_NAME); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?php echo e(FAVICON_URL); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(LOGO_CIRCLE_URL); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=28">
</head>
<body class="admin-body">
    <div class="noise" aria-hidden="true"></div>
    <header class="topbar">
        <?php echo brand_html('nav'); ?>
        <nav class="topnav">
            <a href="index.php">Ver catálogo</a>
            <a class="btn btn-ghost" href="logout.php">Salir</a>
        </nav>
    </header>

    <main class="admin-main">
        <section class="admin-hero">
            <div>
                <p class="eyebrow">Gestión</p>
                <h1>Panel</h1>
                <p>Prepará pedidos de instalación y administrá el catálogo público.</p>
            </div>
        </section>

        <section class="prep-section" id="prep-section" aria-labelledby="prep-title">
            <div class="prep-head">
                <div>
                    <p class="eyebrow">Operaciones</p>
                    <h2 id="prep-title">A preparar</h2>
                    <p class="prep-lede">Pedidos de máquinas para armar: cliente, teléfono y local. Solo se ve acá en el panel.</p>
                </div>
            </div>

            <form id="prep-form" class="form prep-form">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="action" id="prep-action" value="prep_create">
                <input type="hidden" name="id" id="prep-id" value="">

                <label class="prep-field prep-field-machine">
                    <span class="prep-field-title">Máquina</span>
                    <input type="text" name="machine_name" id="prep-machine" maxlength="120" required placeholder="Ej: UFO CATCHER / Camión MACK">
                </label>
                <label class="prep-field prep-field-client">
                    <span class="prep-field-title">Cliente</span>
                    <input type="text" name="client_name" id="prep-client" maxlength="120" required placeholder="Nombre y apellido">
                </label>
                <label class="prep-field prep-field-phone">
                    <span class="prep-field-title">Teléfono / WhatsApp</span>
                    <input type="tel" name="client_phone" id="prep-phone" maxlength="40" inputmode="tel" placeholder="54911...">
                </label>
                <label class="prep-field prep-field-location">
                    <span class="prep-field-title">Ubicación del local</span>
                    <input type="text" name="location" id="prep-location" maxlength="200" required placeholder="Dirección o barrio">
                </label>
                <label class="prep-field prep-field-map">
                    <span class="prep-field-title">Link de Maps <small>(opcional)</small></span>
                    <input type="url" name="map_url" id="prep-map-url" maxlength="500" inputmode="url" placeholder="https://maps.app.goo.gl/...">
                </label>
                <label class="prep-field prep-field-status">
                    <span class="prep-field-title">Estado</span>
                    <select name="status" id="prep-status">
                        <?php foreach (PREP_STATUSES as $key => $label): ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="prep-form-actions">
                    <button class="btn btn-gold" type="submit" id="prep-submit">Agregar pedido</button>
                    <button class="btn btn-ghost" type="button" id="prep-cancel" hidden>Cancelar edición</button>
                </div>
                <p class="flash error" id="prep-error" hidden></p>
            </form>

            <div class="prep-toolbar">
                <label class="check prep-hide-done">
                    <input type="checkbox" id="prep-hide-done">
                    Ocultar entregadas
                </label>
                <span class="prep-count"><?php echo count($prepItems); ?> pedido<?php echo count($prepItems) === 1 ? '' : 's'; ?></span>
            </div>

            <div class="prep-cards" id="prep-cards">
                <?php foreach ($prepItems as $item): ?>
                    <?php
                    $status = (string) ($item['status'] ?? 'pendiente');
                    $wa = prep_whatsapp_link($item);
                    ?>
                    <article
                        class="prep-card status-<?php echo e($status); ?>"
                        data-id="<?php echo e($item['id'] ?? ''); ?>"
                        data-status="<?php echo e($status); ?>"
                        data-machine="<?php echo e($item['machine_name'] ?? ''); ?>"
                        data-client="<?php echo e($item['client_name'] ?? ''); ?>"
                        data-phone="<?php echo e($item['client_phone'] ?? ''); ?>"
                        data-location="<?php echo e($item['location'] ?? ''); ?>"
                        data-map="<?php echo e($item['map_url'] ?? ''); ?>"
                    >
                        <div class="prep-card-top">
                            <h3><?php echo e($item['machine_name'] ?? ''); ?></h3>
                            <span class="prep-badge"><?php echo e(prep_status_label($status)); ?></span>
                        </div>
                        <p class="prep-client"><strong><?php echo e($item['client_name'] ?? ''); ?></strong>
                            <?php if (!empty($item['client_phone'])): ?>
                                · <?php echo e($item['client_phone']); ?>
                            <?php endif; ?>
                        </p>
                        <p class="prep-location"><?php echo e($item['location'] ?? ''); ?></p>
                        <?php if (!empty($item['map_url'])): ?>
                            <a class="prep-map-link" href="<?php echo e($item['map_url']); ?>" target="_blank" rel="noopener">Ver mapa</a>
                        <?php endif; ?>
                        <label class="prep-status-label">
                            Estado
                            <select class="prep-status-select" data-id="<?php echo e($item['id'] ?? ''); ?>" aria-label="Cambiar estado">
                                <?php foreach (PREP_STATUSES as $key => $label): ?>
                                    <option value="<?php echo e($key); ?>" <?php echo $status === $key ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="prep-card-actions">
                            <?php if ($wa !== ''): ?>
                                <a class="btn btn-tiny btn-ghost" href="<?php echo e($wa); ?>" target="_blank" rel="noopener">WhatsApp</a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-tiny btn-ghost js-prep-edit">Editar</button>
                            <button type="button" class="btn btn-tiny btn-danger js-prep-delete">Quitar</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="empty-state prep-empty" id="prep-empty" <?php echo $prepItems ? 'hidden' : ''; ?>>Todavía no hay máquinas a preparar.</p>

            <div class="prep-table-wrap" <?php echo $prepItems ? '' : 'hidden'; ?>>
                <table class="prep-table">
                    <thead>
                        <tr>
                            <th>Máquina</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Local</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prepItems as $item): ?>
                            <?php
                            $status = (string) ($item['status'] ?? 'pendiente');
                            $wa = prep_whatsapp_link($item);
                            ?>
                            <tr
                                class="status-<?php echo e($status); ?>"
                                data-id="<?php echo e($item['id'] ?? ''); ?>"
                                data-status="<?php echo e($status); ?>"
                                data-machine="<?php echo e($item['machine_name'] ?? ''); ?>"
                                data-client="<?php echo e($item['client_name'] ?? ''); ?>"
                                data-phone="<?php echo e($item['client_phone'] ?? ''); ?>"
                                data-location="<?php echo e($item['location'] ?? ''); ?>"
                                data-map="<?php echo e($item['map_url'] ?? ''); ?>"
                            >
                                <td data-label="Máquina"><?php echo e($item['machine_name'] ?? ''); ?></td>
                                <td data-label="Cliente"><?php echo e($item['client_name'] ?? ''); ?></td>
                                <td data-label="Teléfono"><?php echo e($item['client_phone'] ?? '—'); ?></td>
                                <td data-label="Local"><?php echo e($item['location'] ?? ''); ?></td>
                                <td data-label="Estado">
                                    <select class="prep-status-select" data-id="<?php echo e($item['id'] ?? ''); ?>" aria-label="Cambiar estado">
                                        <?php foreach (PREP_STATUSES as $key => $label): ?>
                                            <option value="<?php echo e($key); ?>" <?php echo $status === $key ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td data-label="Acciones">
                                    <div class="prep-table-actions">
                                        <?php if (!empty($item['map_url'])): ?>
                                            <a class="btn btn-tiny btn-ghost" href="<?php echo e($item['map_url']); ?>" target="_blank" rel="noopener">Mapa</a>
                                        <?php endif; ?>
                                        <?php if ($wa !== ''): ?>
                                            <a class="btn btn-tiny btn-ghost" href="<?php echo e($wa); ?>" target="_blank" rel="noopener">WA</a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-tiny btn-ghost js-prep-edit">Editar</button>
                                        <button type="button" class="btn btn-tiny btn-danger js-prep-delete">Quitar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-catalog" aria-labelledby="catalog-title">
            <div class="admin-hero admin-catalog-head">
                <div>
                    <p class="eyebrow">Catálogo público</p>
                    <h2 id="catalog-title">Máquinas del local</h2>
                    <p>Cargá fotos, videos y marcá las que están alquiladas o vendidas.</p>
                </div>
                <button class="btn btn-gold" type="button" id="open-create">+ Agregar máquina</button>
            </div>

            <section class="hero-stats admin-stats">
                <div><em id="stat-available"><?php echo (int) $stats['available']; ?></em><span>disponibles</span></div>
                <div><em id="stat-rented"><?php echo (int) $stats['rented']; ?></em><span>alquiladas</span></div>
                <div><em id="stat-sold"><?php echo (int) $stats['sold']; ?></em><span>vendidas</span></div>
                <div><em id="stat-total"><?php echo (int) $stats['total']; ?></em><span>en el catálogo</span></div>
            </section>

            <section class="admin-list" id="admin-list">
                <?php foreach ($machines as $machine): ?>
                    <?php
                    $rented = !empty($machine['rented']);
                    $sold = !empty($machine['sold']);
                    $slides = machine_media_slides($machine);
                    $cover = $slides[0] ?? ['type' => 'image', 'src' => default_photo(machine_primary_category($machine))];
                    $videos = machine_videos($machine);
                    $stored = stored_machine_photos($machine);
                    $machineCats = machine_categories($machine);
                    $cardClass = 'admin-card';
                    if ($rented) {
                        $cardClass .= ' is-rented';
                    }
                    if ($sold) {
                        $cardClass .= ' is-sold';
                    }
                    ?>
                    <article class="<?php echo $cardClass; ?>" data-id="<?php echo e($machine['id']); ?>">
                        <div class="photo">
                            <?php if (($cover['type'] ?? '') === 'video' && !empty($cover['embed'])): ?>
                                <div class="video-frame">
                                    <iframe src="<?php echo e($cover['embed']); ?>" title="Video" allowfullscreen loading="lazy"></iframe>
                                </div>
                            <?php else: ?>
                                <img src="<?php echo e($cover['src'] ?? default_photo(machine_primary_category($machine))); ?>" alt="">
                            <?php endif; ?>
                            <?php if ($rented): ?>
                                <div class="rented-banner"><span>Alquilada</span></div>
                            <?php elseif ($sold): ?>
                                <div class="sold-banner"><span>Vendida</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="admin-card-body">
                            <div class="cat-pills">
                                <?php foreach ($machineCats as $catKey): ?>
                                    <span class="cat-pill"><?php echo e(category_full($catKey)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <h3><?php echo e($machine['name'] ?? ''); ?></h3>
                            <p><?php echo e($machine['description'] ?? ''); ?></p>
                            <p class="admin-meta">
                                <?php echo count($stored); ?> foto<?php echo count($stored) === 1 ? '' : 's'; ?>
                                · <?php echo count($videos); ?> video<?php echo count($videos) === 1 ? '' : 's'; ?>
                                <?php if ($sold): ?> · Vendida<?php endif; ?>
                            </p>
                            <div class="admin-actions">
                                <button type="button" class="btn btn-tiny js-toggle" data-id="<?php echo e($machine['id']); ?>">
                                    <?php echo $rented ? 'Marcar disponible' : 'Marcar alquilada'; ?>
                                </button>
                                <button type="button" class="btn btn-tiny btn-ghost js-toggle-sold" data-id="<?php echo e($machine['id']); ?>">
                                    <?php echo $sold ? 'Desmarcar vendida' : 'Marcar vendida'; ?>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-tiny btn-ghost js-edit"
                                    data-id="<?php echo e($machine['id']); ?>"
                                    data-name="<?php echo e($machine['name'] ?? ''); ?>"
                                    data-categories="<?php echo e(json_encode($machineCats, JSON_UNESCAPED_UNICODE)); ?>"
                                    data-description="<?php echo e($machine['description'] ?? ''); ?>"
                                    data-rented="<?php echo $rented ? '1' : '0'; ?>"
                                    data-sold="<?php echo $sold ? '1' : '0'; ?>"
                                    data-photos="<?php echo e(json_encode($stored, JSON_UNESCAPED_SLASHES)); ?>"
                                    data-videos="<?php echo e(json_encode($videos, JSON_UNESCAPED_SLASHES)); ?>"
                                >Editar</button>
                                <button type="button" class="btn btn-tiny btn-danger js-delete" data-id="<?php echo e($machine['id']); ?>">Quitar</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
            <p class="empty-state" id="admin-empty" <?php echo count($machines) ? 'hidden' : ''; ?>>Todavía no hay máquinas. Agregá la primera.</p>
        </section>
    </main>

    <div class="modal" id="machine-modal" hidden>
        <div class="modal-card">
            <button class="modal-close" type="button" id="close-modal" aria-label="Cerrar">×</button>
            <h2 id="modal-title">Agregar máquina</h2>
            <form id="machine-form" class="form" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="form-id" value="">
                <input type="hidden" name="keep_photos" id="form-keep-photos" value="[]">

                <label>
                    Nombre
                    <input type="text" name="name" id="form-name" maxlength="80" required placeholder="Ej: Grúa de peluches Jumbo">
                </label>

                <fieldset class="category-checks">
                    <legend>Categorías <small>(podés elegir más de una)</small></legend>
                    <div class="category-check-grid" id="form-categories">
                        <?php foreach (CATEGORIES as $key => $cat): ?>
                            <label class="check">
                                <input type="checkbox" name="categories[]" value="<?php echo e($key); ?>" class="js-category-check">
                                <?php echo e($cat['full']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <label>
                    Descripción
                    <textarea name="description" id="form-description" rows="4" maxlength="600" required placeholder="Qué ofrece, tamaño, para qué local sirve..."></textarea>
                </label>

                <label class="file-label">
                    Fotos
                    <input type="file" name="photos[]" id="form-photos" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                    <small>Podés subir hasta <?php echo (int) MAX_PHOTOS; ?> fotos · JPG, PNG, WEBP o GIF · máx. 5 MB c/u</small>
                </label>
                <div class="photo-keep" id="photo-keep" hidden></div>
                <div class="photo-preview-grid" id="photo-preview-grid" hidden></div>

                <label>
                    Videos (links)
                    <textarea name="videos" id="form-videos" rows="3" placeholder="Un link por línea&#10;YouTube, TikTok o Google Drive"></textarea>
                    <small>Hasta <?php echo (int) MAX_VIDEOS; ?> links. Ejemplo: https://youtu.be/...</small>
                </label>

                <label class="check">
                    <input type="checkbox" name="rented" id="form-rented" value="1">
                    Ya está alquilada (mostrar cartel)
                </label>

                <label class="check">
                    <input type="checkbox" name="sold" id="form-sold" value="1">
                    Ya está vendida (mostrar cartel)
                </label>

                <p class="flash error" id="form-error" hidden></p>
                <button class="btn btn-gold" type="submit" id="form-submit">Guardar máquina</button>
            </form>
        </div>
    </div>

    <script src="assets/js/admin.js?v=8"></script>
</body>
</html>
