<?php
/**
 * Endpoint público: consultar por cédula qué regalo(s) eligió el invitado.
 * Solo devuelve el regalo y el nombre con que se registró; nunca el correo.
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(405, ['ok' => false, 'error' => 'Método no permitido.']);
}

if (!csrf_valido($_POST['csrf'] ?? null)) {
    responder(403, ['ok' => false, 'error' => 'Sesión inválida. Recarga la página e intenta de nuevo.']);
}

$cedula = preg_replace('/\D/', '', (string) ($_POST['cedula'] ?? ''));

if (!cedula_valida($cedula)) {
    responder(422, ['ok' => false, 'error' => 'La cédula debe tener 10 dígitos.']);
}

$stmt = db()->prepare(
    'SELECT g.nombre, g.imagen, g.precio_ref, c.nombre AS invitado, c.creado_en
     FROM claims c
     JOIN gifts g ON g.id = c.gift_id
     WHERE c.cedula = ?
     ORDER BY c.creado_en ASC'
);
$stmt->execute([$cedula]);
$filas = $stmt->fetchAll();

if (!$filas) {
    responder(200, [
        'ok'      => true,
        'regalos' => [],
        'mensaje' => 'No encontramos ninguna elección con esa cédula. Si crees que es un error, escríbenos.',
    ]);
}

$regalos = [];
foreach ($filas as $f) {
    $regalos[] = [
        'nombre'   => $f['nombre'],
        'imagen'   => imagen_src($f['imagen']),
        'precio'   => $f['precio_ref'],
        'invitado' => $f['invitado'],
        'fecha'    => date('d/m/Y', strtotime($f['creado_en'])),
    ];
}

responder(200, ['ok' => true, 'regalos' => $regalos]);
