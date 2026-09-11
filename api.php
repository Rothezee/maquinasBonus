<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_api_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metodo no permitido'], 405);
}

if (!csrf_verify($_POST['csrf'] ?? null)) {
    json_response(['ok' => false, 'error' => 'Sesion vencida. Recarga la pagina.'], 403);
}

$action = (string) ($_POST['action'] ?? '');
$machines = load_machines();

try {
    if ($action === 'create' || $action === 'update') {
        $fields = sanitize_machine_input($_POST);

        if ($action === 'create') {
            $photos = handle_uploads($_FILES['photos'] ?? null, []);
            $fields['id'] = bin2hex(random_bytes(8));
            $fields['photos'] = $photos;
            $fields['photo'] = $photos[0] ?? '';
            $fields['created_at'] = date('Y-m-d H:i:s');
            $machines[] = $fields;
            $saved = $fields;
        } else {
            $id = (string) ($_POST['id'] ?? '');
            $index = null;
            foreach ($machines as $i => $machine) {
                if (($machine['id'] ?? '') === $id) {
                    $index = $i;
                    break;
                }
            }
            if ($index === null) {
                throw new InvalidArgumentException('No encontramos esa maquina.');
            }

            $current = $machines[$index];
            $existing = stored_machine_photos($current);
            $keep = $_POST['keep_photos'] ?? null;
            if ($keep === null) {
                $keepList = $existing;
            } elseif (is_array($keep)) {
                $keepList = $keep;
            } else {
                $decoded = json_decode((string) $keep, true);
                $keepList = is_array($decoded) ? $decoded : [];
            }

            $keepList = array_values(array_filter(array_map(static function ($photo) {
                return str_replace('\\', '/', trim((string) $photo));
            }, $keepList)));

            foreach ($existing as $photo) {
                if (!in_array($photo, $keepList, true)) {
                    delete_upload($photo);
                }
            }

            $photos = handle_uploads($_FILES['photos'] ?? null, $keepList);
            $fields['id'] = $id;
            $fields['photos'] = $photos;
            $fields['photo'] = $photos[0] ?? '';
            $fields['created_at'] = $current['created_at'] ?? date('Y-m-d H:i:s');
            $machines[$index] = $fields;
            $saved = $fields;
        }

        if (!save_machines($machines)) {
            throw new RuntimeException('No se pudo guardar. Revisa permisos de la carpeta data.');
        }

        json_response([
            'ok' => true,
            'machine' => $saved,
            'photo_url' => machine_photo($saved),
            'photos' => machine_photos($saved),
            'videos' => machine_videos($saved),
        ]);
    }

    if ($action === 'toggle' || $action === 'toggle_sold') {
        $id = (string) ($_POST['id'] ?? '');
        $field = $action === 'toggle_sold' ? 'sold' : 'rented';
        $found = false;
        foreach ($machines as &$machine) {
            if (($machine['id'] ?? '') === $id) {
                $machine[$field] = empty($machine[$field]);
                // Compat: limpia el flag viejo si existía
                unset($machine['for_sale']);
                $found = true;
                $saved = $machine;
                break;
            }
        }
        unset($machine);

        if (!$found) {
            throw new InvalidArgumentException('No encontramos esa maquina.');
        }
        if (!save_machines($machines)) {
            throw new RuntimeException('No se pudo guardar el estado.');
        }
        json_response(['ok' => true, 'machine' => $saved]);
    }

    if ($action === 'delete') {
        $id = (string) ($_POST['id'] ?? '');
        $kept = [];
        $deleted = null;
        foreach ($machines as $machine) {
            if (($machine['id'] ?? '') === $id) {
                $deleted = $machine;
                continue;
            }
            $kept[] = $machine;
        }
        if ($deleted === null) {
            throw new InvalidArgumentException('No encontramos esa maquina.');
        }
        foreach (stored_machine_photos($deleted) as $photo) {
            delete_upload($photo);
        }
        if (!save_machines($kept)) {
            throw new RuntimeException('No se pudo eliminar.');
        }
        json_response(['ok' => true]);
    }

    // --- Prep queue (máquinas a preparar) ---
    if (strpos($action, 'prep_') === 0) {
        $prep = load_prep();

        if ($action === 'prep_create' || $action === 'prep_update') {
            $fields = sanitize_prep_input($_POST);
            $now = date('Y-m-d H:i:s');

            if ($action === 'prep_create') {
                $fields['id'] = bin2hex(random_bytes(8));
                $fields['created_at'] = $now;
                $fields['updated_at'] = $now;
                $prep[] = $fields;
                $saved = $fields;
            } else {
                $id = (string) ($_POST['id'] ?? '');
                $index = null;
                foreach ($prep as $i => $item) {
                    if (($item['id'] ?? '') === $id) {
                        $index = $i;
                        break;
                    }
                }
                if ($index === null) {
                    throw new InvalidArgumentException('No encontramos ese pedido.');
                }
                $fields['id'] = $id;
                $fields['created_at'] = $prep[$index]['created_at'] ?? $now;
                $fields['updated_at'] = $now;
                $prep[$index] = $fields;
                $saved = $fields;
            }

            if (!save_prep($prep)) {
                throw new RuntimeException('No se pudo guardar. Revisá permisos de la carpeta data.');
            }

            json_response([
                'ok' => true,
                'item' => $saved,
                'whatsapp' => prep_whatsapp_link($saved),
                'status_label' => prep_status_label($saved['status']),
            ]);
        }

        if ($action === 'prep_set_status') {
            $id = (string) ($_POST['id'] ?? '');
            $status = trim((string) ($_POST['status'] ?? ''));
            if (!isset(PREP_STATUSES[$status])) {
                throw new InvalidArgumentException('Estado no válido.');
            }
            $found = false;
            $saved = null;
            foreach ($prep as &$item) {
                if (($item['id'] ?? '') === $id) {
                    $item['status'] = $status;
                    $item['updated_at'] = date('Y-m-d H:i:s');
                    $found = true;
                    $saved = $item;
                    break;
                }
            }
            unset($item);
            if (!$found) {
                throw new InvalidArgumentException('No encontramos ese pedido.');
            }
            if (!save_prep($prep)) {
                throw new RuntimeException('No se pudo actualizar el estado.');
            }
            json_response([
                'ok' => true,
                'item' => $saved,
                'whatsapp' => prep_whatsapp_link($saved),
                'status_label' => prep_status_label($saved['status']),
            ]);
        }

        if ($action === 'prep_delete') {
            $id = (string) ($_POST['id'] ?? '');
            $kept = [];
            $deleted = null;
            foreach ($prep as $item) {
                if (($item['id'] ?? '') === $id) {
                    $deleted = $item;
                    continue;
                }
                $kept[] = $item;
            }
            if ($deleted === null) {
                throw new InvalidArgumentException('No encontramos ese pedido.');
            }
            if (!save_prep($kept)) {
                throw new RuntimeException('No se pudo eliminar.');
            }
            json_response(['ok' => true]);
        }

        json_response(['ok' => false, 'error' => 'Accion de prep desconocida'], 400);
    }

    json_response(['ok' => false, 'error' => 'Accion desconocida'], 400);
} catch (InvalidArgumentException $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
