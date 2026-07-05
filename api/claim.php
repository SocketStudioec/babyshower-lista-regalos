<?php
/**
 * Endpoint público: elegir un regalo.
 * Recibe POST JSON/form: gift_id, nombre, cedula, correo, csrf.
 * Los datos personales solo se muestran en el panel de administración.
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

$entrada = $_POST;
if (empty($entrada) && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $entrada = json_decode(file_get_contents('php://input'), true) ?: [];
}

if (!csrf_valido($entrada['csrf'] ?? null)) {
    responder(403, ['ok' => false, 'error' => 'Sesión inválida. Recarga la página e intenta de nuevo.']);
}

$giftId = (int) ($entrada['gift_id'] ?? 0);
$nombre = trim((string) ($entrada['nombre'] ?? ''));
$cedula = preg_replace('/\D/', '', (string) ($entrada['cedula'] ?? ''));
$correo = trim((string) ($entrada['correo'] ?? ''));

if ($giftId <= 0) {
    responder(422, ['ok' => false, 'error' => 'Regalo inválido.']);
}
if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 150) {
    responder(422, ['ok' => false, 'error' => 'Cuéntanos tu nombre completo, por favor.']);
}
if (!cedula_valida($cedula)) {
    responder(422, ['ok' => false, 'error' => 'La cédula debe tener 10 dígitos.']);
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) {
    responder(422, ['ok' => false, 'error' => 'Ingresa un correo válido, por favor.']);
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Bloquea la fila del regalo para evitar elecciones simultáneas
    $stmt = $pdo->prepare('SELECT g.id, g.nombre, (c.id IS NOT NULL) AS elegido
                           FROM gifts g
                           LEFT JOIN claims c ON c.gift_id = g.id
                           WHERE g.id = ? AND g.activo = 1
                           FOR UPDATE');
    $stmt->execute([$giftId]);
    $regalo = $stmt->fetch();

    if (!$regalo) {
        $pdo->rollBack();
        responder(404, ['ok' => false, 'error' => 'Este regalo ya no está disponible en la lista.']);
    }
    if ($regalo['elegido']) {
        $pdo->rollBack();
        responder(409, ['ok' => false, 'error' => 'Alguien acaba de elegir este regalo. ¡Gracias de todos modos! Puedes escoger otro.']);
    }

    $stmt = $pdo->prepare('INSERT INTO claims (gift_id, nombre, cedula, correo) VALUES (?, ?, ?, ?)');
    $stmt->execute([$giftId, $nombre, $cedula, $correo]);
    $pdo->commit();

    responder(200, [
        'ok'      => true,
        'mensaje' => '¡Gracias de corazón! Reservamos “' . $regalo['nombre'] . '” a tu nombre.',
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 23000 = violación de UNIQUE (elección simultánea)
    if ($e->getCode() === '23000') {
        responder(409, ['ok' => false, 'error' => 'Alguien acaba de elegir este regalo. Puedes escoger otro.']);
    }
    responder(500, ['ok' => false, 'error' => 'Ocurrió un error inesperado. Intenta de nuevo en un momento.']);
}
