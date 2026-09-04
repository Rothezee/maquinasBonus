<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_login();

$machines = load_machines();
$stats = stats($machines);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel — <?php echo e(SITE_NAME); ?></title>
    <link rel="icon" href="<?php echo e(FAVICON_URL); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(LOGO_CIRCLE_URL); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
                <h1>Máquinas del local</h1>
                <p>Cargá fotos, descripciones y marcá las que están alquiladas. El cartel se ve al instante en la web.</p>
            </div>
            <button class="btn btn-gold" type="button" id="open-create">+ Agregar máquina</button>
        </section>

        <section class="hero-stats admin-stats">
            <div><em id="stat-available"><?php echo (int) $stats['available']; ?></em><span>disponibles</span></div>
            <div><em id="stat-rented"><?php echo (int) $stats['rented']; ?></em><span>alquiladas</span></div>
            <div><em id="stat-categories"><?php echo (int) $stats['categories']; ?></em><span>categorías</span></div>
            <div><em id="stat-total"><?php echo (int) $stats['total']; ?></em><span>en el catálogo</span></div>
        </section>

        <section class="admin-list" id="admin-list">
            <?php foreach ($machines as $machine): ?>
                <?php $rented = !empty($machine['rented']); ?>
                <article class="admin-card<?php echo $rented ? ' is-rented' : ''; ?>" data-id="<?php echo e($machine['id']); ?>">
                    <div class="photo">
                        <img src="<?php echo e(machine_photo($machine)); ?>" alt="">
                        <?php if ($rented): ?>
                            <div class="rented-banner"><span>Alquilada</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-card-body">
                        <p class="cat-pill"><?php echo e(category_full($machine['category'] ?? '')); ?></p>
                        <h3><?php echo e($machine['name'] ?? ''); ?></h3>
                        <p><?php echo e($machine['description'] ?? ''); ?></p>
                        <div class="admin-actions">
                            <button type="button" class="btn btn-tiny js-toggle" data-id="<?php echo e($machine['id']); ?>">
                                <?php echo $rented ? 'Marcar disponible' : 'Marcar alquilada'; ?>
                            </button>
                            <button
                                type="button"
                                class="btn btn-tiny btn-ghost js-edit"
                                data-id="<?php echo e($machine['id']); ?>"
                                data-name="<?php echo e($machine['name'] ?? ''); ?>"
                                data-category="<?php echo e($machine['category'] ?? ''); ?>"
                                data-description="<?php echo e($machine['description'] ?? ''); ?>"
                                data-rented="<?php echo $rented ? '1' : '0'; ?>"
                                data-photo="<?php echo e(machine_photo($machine)); ?>"
                            >Editar</button>
                            <button type="button" class="btn btn-tiny btn-danger js-delete" data-id="<?php echo e($machine['id']); ?>">Quitar</button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
        <p class="empty-state" id="admin-empty" <?php echo count($machines) ? 'hidden' : ''; ?>>Todavía no hay máquinas. Agregá la primera.</p>
    </main>

    <div class="modal" id="machine-modal" hidden>
        <div class="modal-card">
            <button class="modal-close" type="button" id="close-modal" aria-label="Cerrar">×</button>
            <h2 id="modal-title">Agregar máquina</h2>
            <form id="machine-form" class="form" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="form-id" value="">

                <label>
                    Nombre
                    <input type="text" name="name" id="form-name" maxlength="80" required placeholder="Ej: Grúa de peluches Jumbo">
                </label>

                <label>
                    Categoría
                    <select name="category" id="form-category" required>
                        <option value="">Elegí una</option>
                        <?php foreach (CATEGORIES as $key => $cat): ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($cat['full']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Descripción
                    <textarea name="description" id="form-description" rows="4" maxlength="600" required placeholder="Qué ofrece, tamaño, para qué local sirve..."></textarea>
                </label>

                <label class="file-label">
                    Foto
                    <input type="file" name="photo" id="form-photo" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small>JPG, PNG, WEBP o GIF · máx. 5 MB</small>
                </label>
                <img class="preview" id="form-preview" alt="Vista previa" hidden>

                <label class="check">
                    <input type="checkbox" name="rented" id="form-rented" value="1">
                    Ya está alquilada (mostrar cartel)
                </label>

                <p class="flash error" id="form-error" hidden></p>
                <button class="btn btn-gold" type="submit" id="form-submit">Guardar máquina</button>
            </form>
        </div>
    </div>

    <script src="assets/js/admin.js"></script>
</body>
</html>
