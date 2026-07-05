<?php
/**
 * Autenticación del administrador.
 */

require_once __DIR__ . '/functions.php';

function admin_autenticado(): bool
{
    iniciar_sesion();
    return !empty($_SESSION['admin_id']);
}

/** Redirige al login si no hay sesión de administrador. */
function requerir_admin(): void
{
    if (!admin_autenticado()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Intenta iniciar sesión. Devuelve true si las credenciales son correctas.
 * Incluye un freno simple contra fuerza bruta por sesión.
 */
function admin_login(string $usuario, string $clave): bool
{
    iniciar_sesion();

    $intentos = $_SESSION['login_intentos'] ?? 0;
    $ultimo   = $_SESSION['login_ultimo'] ?? 0;
    if ($intentos >= 6 && (time() - $ultimo) < 300) {
        return false; // bloqueado 5 minutos tras 6 intentos fallidos
    }

    $stmt = db()->prepare('SELECT id, clave_hash FROM admins WHERE usuario = ?');
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($clave, $admin['clave_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']       = (int) $admin['id'];
        $_SESSION['login_intentos'] = 0;
        return true;
    }

    $_SESSION['login_intentos'] = $intentos + 1;
    $_SESSION['login_ultimo']   = time();
    return false;
}

function admin_logout(): void
{
    iniciar_sesion();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
