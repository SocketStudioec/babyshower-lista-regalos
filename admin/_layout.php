<?php
/**
 * Layout compartido del panel de administración.
 */

require_once __DIR__ . '/../includes/auth.php';

function admin_cabecera(string $titulo, bool $conNav = true): void
{
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titulo) ?> · Panel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300..700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='14' fill='%2343333A'/><circle cx='16' cy='16' r='7' fill='%23E8B4C0'/></svg>">
</head>
<body>
    <?php if ($conNav): ?>
<nav class="nav">
    <span class="nav-marca">Baby Shower · Panel</span>
    <div class="nav-enlaces">
        <a href="index.php">Regalos</a>
        <a href="elecciones.php">Elecciones</a>
        <a href="ajustes.php">Ajustes</a>
        <a href="../" target="_blank" rel="noopener">Ver sitio público</a>
        <a class="nav-salir" href="logout.php">Salir</a>
    </div>
</nav>
    <?php endif; ?>
<main class="contenido">
    <?php
}

function admin_pie(): void
{
    ?>
</main>
</body>
</html>
    <?php
}

/** Mensaje flash de una sola lectura. */
function flash_set(string $tipo, string $texto): void
{
    iniciar_sesion();
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function flash_html(): string
{
    iniciar_sesion();
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="flash flash-' . e($f['tipo']) . '">' . e($f['texto']) . '</div>';
}
