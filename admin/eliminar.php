<?php
require_once __DIR__ . '/_layout.php';
requerir_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_valido($_POST['csrf'] ?? null)) {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    // Borra también la imagen local si existía
    $stmt = db()->prepare('SELECT imagen FROM gifts WHERE id = ?');
    $stmt->execute([$id]);
    $imagen = $stmt->fetchColumn();

    $stmt = db()->prepare('DELETE FROM gifts WHERE id = ?');
    $stmt->execute([$id]);

    if ($imagen && !preg_match('#^https?://#i', $imagen)) {
        $ruta = UPLOADS_DIR . '/' . basename($imagen);
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }
    flash_set('ok', 'Regalo eliminado.');
}

header('Location: index.php');
exit;
