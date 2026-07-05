<?php
require_once __DIR__ . '/_layout.php';
requerir_admin();

$errores = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $errores[] = 'Sesión expirada. Vuelve a intentarlo.';
    } else {
        $nombreBebe = trim($_POST['nombre_bebe'] ?? '');
        $titulo     = trim($_POST['titulo'] ?? '');
        $mensaje    = trim($_POST['mensaje'] ?? '');
        $fecha      = trim($_POST['fecha_evento'] ?? '');
        $lugar      = trim($_POST['lugar'] ?? '');

        if ($nombreBebe === '' || $titulo === '') {
            $errores[] = 'El nombre de la bebé y el título son obligatorios.';
        } else {
            $stmt = db()->prepare(
                'UPDATE settings SET nombre_bebe=?, titulo=?, mensaje=?, fecha_evento=?, lugar=? WHERE id=1'
            );
            $stmt->execute([$nombreBebe, $titulo, $mensaje, $fecha, $lugar]);
            flash_set('ok', 'Ajustes guardados. Así lo verán tus invitados.');
            header('Location: ajustes.php');
            exit;
        }
    }
}

$ajustes = obtener_ajustes();

admin_cabecera('Ajustes');
?>
<?= flash_html() ?>

<div class="encabezado">
    <div>
        <h1 class="titulo">Ajustes del evento</h1>
        <p class="subtitulo">Personaliza el mensaje y los datos que ven los invitados.</p>
    </div>
    <a class="btn btn-secundario" href="index.php">Volver</a>
</div>

<?php foreach ($errores as $err): ?>
<div class="flash flash-error"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" class="formulario tarjeta">
    <?= csrf_field() ?>
    <div class="fila-doble">
        <label class="campo">
            <span>Nombre de la bebé *</span>
            <input type="text" name="nombre_bebe" maxlength="100" required value="<?= e($ajustes['nombre_bebe']) ?>">
        </label>
        <label class="campo">
            <span>Título de la página *</span>
            <input type="text" name="titulo" maxlength="200" required value="<?= e($ajustes['titulo']) ?>">
        </label>
    </div>
    <label class="campo">
        <span>Mensaje de bienvenida</span>
        <textarea name="mensaje" rows="5"><?= e($ajustes['mensaje']) ?></textarea>
    </label>
    <div class="fila-doble">
        <label class="campo">
            <span>Fecha del evento (texto libre)</span>
            <input type="text" name="fecha_evento" maxlength="120" value="<?= e($ajustes['fecha_evento']) ?>" placeholder="Sábado 15 de agosto, 15h00">
        </label>
        <label class="campo">
            <span>Lugar</span>
            <input type="text" name="lugar" maxlength="200" value="<?= e($ajustes['lugar']) ?>" placeholder="Salón de eventos …">
        </label>
    </div>
    <button type="submit" class="btn btn-primario">Guardar ajustes</button>
</form>
<?php admin_pie(); ?>
