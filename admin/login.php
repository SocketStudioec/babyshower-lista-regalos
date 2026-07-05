<?php
require_once __DIR__ . '/_layout.php';

if (admin_autenticado()) {
    header('Location: index.php');
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $error = 'Sesión expirada. Intenta de nuevo.';
    } elseif (admin_login(trim($_POST['usuario'] ?? ''), $_POST['clave'] ?? '')) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}

admin_cabecera('Ingresar', false);
?>
<div class="login-caja">
    <span class="eyebrow">Panel de administración</span>
    <h1 class="login-titulo">Lista de Regalos</h1>
    <p class="login-sub">Acceso exclusivo para los papás.</p>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="formulario">
        <?= csrf_field() ?>
        <label class="campo">
            <span>Usuario</span>
            <input type="text" name="usuario" required autofocus autocomplete="username">
        </label>
        <label class="campo">
            <span>Contraseña</span>
            <input type="password" name="clave" required autocomplete="current-password">
        </label>
        <button type="submit" class="btn btn-primario">Ingresar</button>
    </form>
</div>
<?php admin_pie(); ?>
