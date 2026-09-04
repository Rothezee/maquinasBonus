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
            $fields['id'] = bin2hex(random_bytes(8));
            $fields['photo'] = handle_upload($_FILES['photo'] ?? null) ?: '';
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
            $fields['id'] = $id;
            $fields['photo'] = handle_upload($_FILES['photo'] ?? null, $machines[$index]['photo'] ?? null);
            $fields['created_at'] = $machines[$index]['created_at'] ?? date('Y-m-d H:i:s');
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
        ]);
    }

    if ($action === 'toggle') {
        $id = (string) ($_POST['id'] ?? '');
        $found = false;
        foreach ($machines as &$machine) {
            if (($machine['id'] ?? '') === $id) {
                $machine['rented'] = empty($machine['rented']);
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
        if (!empty($deleted['photo']) && strpos($deleted['photo'], 'uploads/') === 0) {
            $old = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $deleted['photo']);
            if (is_file($old)) {
                @unlink($old);
            }
        }
        if (!save_machines($kept)) {
            throw new RuntimeException('No se pudo eliminar.');
        }
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Accion desconocida'], 400);
} catch (InvalidArgumentException $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
