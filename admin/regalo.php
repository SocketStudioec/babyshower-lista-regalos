<?php
require_once __DIR__ . '/_layout.php';
requerir_admin();

$id     = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$regalo = null;

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM gifts WHERE id = ?');
    $stmt->execute([$id]);
    $regalo = $stmt->fetch();
    if (!$regalo) {
        flash_set('error', 'El regalo no existe.');
        header('Location: index.php');
        exit;
    }
}

$errores = [];

/** Procesa la imagen subida; devuelve el nombre de archivo o null. */
function procesar_imagen(array &$errores): ?string
{
    if (empty($_FILES['imagen_archivo']['name'])) {
        return null;
    }
    $archivo = $_FILES['imagen_archivo'];
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'No se pudo subir la imagen (código ' . (int) $archivo['error'] . ').';
        return null;
    }
    if ($archivo['size'] > UPLOAD_MAX_BYTES) {
        $errores[] = 'La imagen supera el máximo de 4 MB.';
        return null;
    }
    $info = @getimagesize($archivo['tmp_name']);
    $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($permitidos[$info[2]])) {
        $errores[] = 'Formato no permitido. Usa JPG, PNG o WEBP.';
        return null;
    }
    $nombre = bin2hex(random_bytes(10)) . '.' . $permitidos[$info[2]];
    if (!is_dir(UPLOADS_DIR)) {
        @mkdir(UPLOADS_DIR, 0755, true);
    }
    if (!move_uploaded_file($archivo['tmp_name'], UPLOADS_DIR . '/' . $nombre)) {
        $errores[] = 'No se pudo guardar la imagen en el servidor.';
        return null;
    }
    return $nombre;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $errores[] = 'Sesión expirada. Vuelve a intentarlo.';
    }

    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $indicaciones = trim($_POST['indicaciones'] ?? '');
    $enlaces      = trim($_POST['enlaces'] ?? '');
    $imagenUrl    = trim($_POST['imagen_url'] ?? '');
    $prioridad    = in_array($_POST['prioridad'] ?? '', ['alta', 'media', 'baja'], true) ? $_POST['prioridad'] : 'media';
    $precioRef    = trim($_POST['precio_ref'] ?? '');
    $orden        = (int) ($_POST['orden'] ?? 0);
    $activo       = isset($_POST['activo']) ? 1 : 0;

    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 200) {
        $errores[] = 'El nombre del regalo es obligatorio (2 a 200 caracteres).';
    }
    if ($imagenUrl !== '' && !filter_var($imagenUrl, FILTER_VALIDATE_URL)) {
        $errores[] = 'La URL de imagen no es válida.';
    }

    $imagenSubida = procesar_imagen($errores);

    if (!$errores) {
        // Prioridad: archivo subido > URL externa > imagen actual
        $imagen = $imagenSubida ?? ($imagenUrl !== '' ? $imagenUrl : ($regalo['imagen'] ?? null));

        if ($regalo) {
            $stmt = db()->prepare(
                'UPDATE gifts SET nombre=?, descripcion=?, indicaciones=?, enlaces=?, imagen=?, prioridad=?, precio_ref=?, orden=?, activo=? WHERE id=?'
            );
            $stmt->execute([$nombre, $descripcion, $indicaciones, $enlaces, $imagen, $prioridad, $precioRef, $orden, $activo, $id]);
            flash_set('ok', 'Regalo actualizado.');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO gifts (nombre, descripcion, indicaciones, enlaces, imagen, prioridad, precio_ref, orden, activo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$nombre, $descripcion, $indicaciones, $enlaces, $imagen, $prioridad, $precioRef, $orden, $activo]);
            flash_set('ok', 'Regalo agregado a la lista.');
        }
        header('Location: index.php');
        exit;
    }

    // Repoblar el formulario con lo enviado
    $regalo = array_merge($regalo ?? [], [
        'nombre' => $nombre, 'descripcion' => $descripcion, 'indicaciones' => $indicaciones,
        'enlaces' => $enlaces, 'prioridad' => $prioridad, 'precio_ref' => $precioRef,
        'orden' => $orden, 'activo' => $activo, 'imagen' => $regalo['imagen'] ?? null,
    ]);
}

$v = function (string $k, $def = '') use ($regalo) {
    return e((string) ($regalo[$k] ?? $def));
};
$imgActual = imagen_src($regalo['imagen'] ?? '', '../');

admin_cabecera($id ? 'Editar regalo' : 'Nuevo regalo');
?>
<div class="encabezado">
    <div>
        <h1 class="titulo"><?= $id ? 'Editar regalo' : 'Nuevo regalo' ?></h1>
        <p class="subtitulo">Los invitados verán todo tal como lo escribas aquí.</p>
    </div>
    <a class="btn btn-secundario" href="index.php">Volver</a>
</div>

<?php foreach ($errores as $err): ?>
<div class="flash flash-error"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="formulario tarjeta">
    <?= csrf_field() ?>
    <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <label class="campo">
        <span>Nombre del regalo *</span>
        <input type="text" name="nombre" maxlength="200" required value="<?= $v('nombre') ?>" placeholder="Coche de paseo, pañales etapa 1, esterilizador…">
    </label>

    <label class="campo">
        <span>Descripción</span>
        <textarea name="descripcion" rows="3" placeholder="Breve descripción visible en la tarjeta."><?= $v('descripcion') ?></textarea>
    </label>

    <label class="campo">
        <span>Indicaciones (talla, color, marca preferida…)</span>
        <textarea name="indicaciones" rows="3" placeholder="Ej.: talla 0-3 meses, tonos rosados o neutros."><?= $v('indicaciones') ?></textarea>
    </label>

    <label class="campo">
        <span>Enlaces donde comprar (uno por línea)</span>
        <textarea name="enlaces" rows="3" placeholder="https://www.pycca.com/...&#10;https://www.amazon.com/..."><?= $v('enlaces') ?></textarea>
    </label>

    <div class="fila-doble">
        <label class="campo">
            <span>Imagen (subir archivo JPG/PNG/WEBP, máx. 4 MB)</span>
            <input type="file" name="imagen_archivo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        </label>
        <label class="campo">
            <span>… o URL de imagen externa</span>
            <input type="url" name="imagen_url" placeholder="https://..." value="">
        </label>
    </div>

    <?php if ($imgActual): ?>
    <div class="imagen-actual">
        <img src="<?= e($imgActual) ?>" alt="Imagen actual del regalo">
        <small>Imagen actual — se reemplaza si subes otra o indicas una URL.</small>
    </div>
    <?php endif; ?>

    <div class="fila-triple">
        <label class="campo">
            <span>Prioridad</span>
            <select name="prioridad">
                <?php foreach (['alta' => 'Alta — nos ayuda muchísimo', 'media' => 'Media — nos hace ilusión', 'baja' => 'Baja — un detalle extra'] as $val => $texto): ?>
                <option value="<?= $val ?>" <?= ($regalo['prioridad'] ?? 'media') === $val ? 'selected' : '' ?>><?= $texto ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="campo">
            <span>Precio referencial (opcional)</span>
            <input type="text" name="precio_ref" maxlength="60" value="<?= $v('precio_ref') ?>" placeholder="$45 aprox.">
        </label>
        <label class="campo">
            <span>Orden en la lista</span>
            <input type="number" name="orden" value="<?= (int) ($regalo['orden'] ?? 0) ?>">
        </label>
    </div>

    <label class="check">
        <input type="checkbox" name="activo" <?= (int) ($regalo['activo'] ?? 1) === 1 ? 'checked' : '' ?>>
        <span>Visible en la lista pública</span>
    </label>

    <button type="submit" class="btn btn-primario"><?= $id ? 'Guardar cambios' : 'Agregar a la lista' ?></button>
</form>
<?php admin_pie(); ?>
