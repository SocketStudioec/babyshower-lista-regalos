<?php
require_once __DIR__ . '/_layout.php';
requerir_admin();

$claims = db()->query(
    'SELECT c.*, g.nombre AS regalo
     FROM claims c
     JOIN gifts g ON g.id = c.gift_id
     ORDER BY c.creado_en DESC'
)->fetchAll();

admin_cabecera('Elecciones');
?>
<?= flash_html() ?>

<div class="encabezado">
    <div>
        <h1 class="titulo">Elecciones de los invitados</h1>
        <p class="subtitulo">Estos datos son privados: solo se ven en este panel.</p>
    </div>
    <a class="btn btn-secundario" href="index.php">Volver a regalos</a>
</div>

<?php if (!$claims): ?>
<div class="vacio-admin">
    <p>Todavía nadie ha elegido un regalo. Comparte el enlace público con tus invitados.</p>
</div>
<?php else: ?>
<div class="tabla-envoltura">
<table class="tabla">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Invitado</th>
            <th>Cédula</th>
            <th>Correo</th>
            <th>Regalo elegido</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($claims as $c): ?>
        <tr>
            <td><?= e(date('d/m/Y H:i', strtotime($c['creado_en']))) ?></td>
            <td><strong><?= e($c['nombre']) ?></strong></td>
            <td><?= e($c['cedula']) ?></td>
            <td><a href="mailto:<?= e($c['correo']) ?>"><?= e($c['correo']) ?></a></td>
            <td><?= e($c['regalo']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php admin_pie(); ?>
