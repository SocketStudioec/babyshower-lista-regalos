<?php
require_once __DIR__ . '/_layout.php';
requerir_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrf_valido($_POST['csrf'] ?? null)) {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM claims WHERE gift_id = ?');
    $stmt->execute([$id]);
    flash_set('ok', 'El regalo vuelve a estar disponible para los invitados.');
}

header('Location: index.php');
exit;
