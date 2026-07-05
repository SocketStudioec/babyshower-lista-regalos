<?php
require_once __DIR__ . '/_layout.php';
requerir_admin();

$regalos = db()->query(
    'SELECT g.*, c.nombre AS claim_nombre, c.cedula AS claim_cedula, c.correo AS claim_correo, c.creado_en AS claim_fecha
     FROM gifts g
     LEFT JOIN claims c ON c.gift_id = g.id
     ORDER BY g.orden ASC, g.creado_en ASC, g.id ASC'
)->fetchAll();

$total    = count($regalos);
$elegidos = count(array_filter($regalos, function ($r) { return $r['claim_nombre'] !== null; }));

admin_cabecera('Regalos');
?>
<?= flash_html() ?>

<div class="encabezado">
    <div>
        <h1 class="titulo">Regalos</h1>
        <p class="subtitulo"><?= $elegidos ?> de <?= $total ?> ya fueron elegidos por los invitados.</p>
    </div>
    <a class="btn btn-primario" href="regalo.php">Agregar regalo</a>
</div>

<?php if (!$regalos): ?>
<div class="vacio-admin">
    <p>Aún no has cargado regalos. Empieza agregando el primero de la lista.</p>
</div>
<?php else: ?>
<div class="tabla-envoltura">
<table class="tabla">
    <thead>
        <tr>
            <th>Regalo</th>
            <th>Prioridad</th>
            <th>Estado</th>
            <th>Elegido por</th>
            <th class="th-acciones">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($regalos as $r): $img = imagen_src($r['imagen'], '../'); ?>
        <tr class="<?= $r['activo'] ? '' : 'fila-inactiva' ?>">
            <td>
                <div class="celda-regalo">
                    <?php if ($img): ?>
                    <img class="mini" src="<?= e($img) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <span class="mini mini-vacia"></span>
                    <?php endif; ?>
                    <div>
                        <strong><?= e($r['nombre']) ?></strong>
                        <?php if (!empty($r['precio_ref'])): ?><small>Ref. <?= e($r['precio_ref']) ?></small><?php endif; ?>
                        <?php if (!$r['activo']): ?><small class="etiqueta-oculto">Oculto en la lista pública</small><?php endif; ?>
                    </div>
                </div>
            </td>
            <td><span class="pill pill-<?= e($r['prioridad']) ?>"><?= e(ucfirst($r['prioridad'])) ?></span></td>
            <td>
                <?php if ($r['claim_nombre'] !== null): ?>
                <span class="pill pill-elegido">Elegido</span>
                <?php else: ?>
                <span class="pill pill-disponible">Disponible</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($r['claim_nombre'] !== null): ?>
                <div class="celda-claim">
                    <strong><?= e($r['claim_nombre']) ?></strong>
                    <small>CI <?= e($r['claim_cedula']) ?> · <a href="mailto:<?= e($r['claim_correo']) ?>"><?= e($r['claim_correo']) ?></a></small>
                    <small><?= e(date('d/m/Y H:i', strtotime($r['claim_fecha']))) ?></small>
                </div>
                <?php else: ?>
                <span class="texto-suave">—</span>
                <?php endif; ?>
            </td>
            <td class="td-acciones">
                <a class="accion" href="regalo.php?id=<?= (int) $r['id'] ?>">Editar</a>
                <?php if ($r['claim_nombre'] !== null): ?>
                <form method="post" action="liberar.php" onsubmit="return confirm('¿Liberar este regalo? La elección del invitado se eliminará y volverá a estar disponible.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="accion accion-alerta" type="submit">Liberar</button>
                </form>
                <?php endif; ?>
                <form method="post" action="eliminar.php" onsubmit="return confirm('¿Eliminar definitivamente este regalo?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <button class="accion accion-peligro" type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php admin_pie(); ?>
