<?php
/**
 * Funciones utilitarias compartidas (público + administración).
 */

require_once __DIR__ . '/db.php';

/** Sesión con cookies endurecidas. */
function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $seguro = !empty($_SERVER['HTTPS']);
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => $seguro,
            ]);
        } else {
            // Compatibilidad PHP < 7.3: samesite vía truco en el path
            session_set_cookie_params(0, '/; samesite=Lax', '', $seguro, true);
        }
        session_start();
    }
}

/** Escape HTML corto. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Token CSRF de la sesión. */
function csrf_token(): string
{
    iniciar_sesion();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto CSRF para formularios. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Verifica el token CSRF recibido. */
function csrf_valido(?string $token): bool
{
    iniciar_sesion();
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

/** Ajustes del evento (fila única). */
function obtener_ajustes(): array
{
    $fila = db()->query('SELECT * FROM settings WHERE id = 1')->fetch();
    return $fila ?: [
        'nombre_bebe'  => 'Nuestra Bebé',
        'titulo'       => 'Lista de Regalos',
        'mensaje'      => '',
        'fecha_evento' => '',
        'lugar'        => '',
    ];
}

/**
 * Convierte el texto de enlaces (uno por línea) en arreglo [['url','label'], ...].
 * La etiqueta se deduce del dominio para mostrar botones legibles.
 */
function parsear_enlaces(?string $texto): array
{
    $resultado = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $texto) as $linea) {
        $url = trim($linea);
        if ($url === '') {
            continue;
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            continue;
        }
        $host  = parse_url($url, PHP_URL_HOST) ?: 'tienda';
        $host  = preg_replace('/^www\./i', '', $host);
        $label = ucfirst(explode('.', $host)[0]);
        $resultado[] = ['url' => $url, 'label' => $label];
    }
    return $resultado;
}

/**
 * Resuelve la ruta pública de la imagen de un regalo.
 * Acepta URL absoluta o nombre de archivo dentro de /uploads.
 * $prefijo permite ajustar la ruta relativa desde /admin.
 */
function imagen_src(?string $imagen, string $prefijo = ''): string
{
    $imagen = trim((string) $imagen);
    if ($imagen === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $imagen)) {
        return $imagen;
    }
    return $prefijo . 'uploads/' . rawurlencode($imagen);
}

/** Cédula ecuatoriana: 10 dígitos numéricos. */
function cedula_valida(string $cedula): bool
{
    return (bool) preg_match('/^[0-9]{10}$/', $cedula);
}

/** Etiquetas de prioridad mostradas al público. */
function etiqueta_prioridad(string $prioridad): string
{
    switch ($prioridad) {
        case 'alta':
            return 'Nos ayuda muchísimo';
        case 'baja':
            return 'Un detalle extra';
        default:
            return 'Nos hace ilusión';
    }
}

/** Regalos activos con su estado de elección (sin datos personales). */
function regalos_publicos(): array
{
    $sql = 'SELECT g.id, g.nombre, g.descripcion, g.indicaciones, g.enlaces, g.imagen,
                   g.prioridad, g.precio_ref,
                   (c.id IS NOT NULL) AS elegido
            FROM gifts g
            LEFT JOIN claims c ON c.gift_id = g.id
            WHERE g.activo = 1
            ORDER BY g.orden ASC, g.creado_en ASC, g.id ASC';
    return db()->query($sql)->fetchAll();
}
