<?php
require_once __DIR__ . '/includes/functions.php';

iniciar_sesion();

$ajustes  = obtener_ajustes();
$regalos  = regalos_publicos();
$total    = count($regalos);
$elegidos = count(array_filter($regalos, function ($r) { return $r['elegido']; }));
$csrf     = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($ajustes['titulo']) ?> · <?= e($ajustes['nombre_bebe']) ?></title>
<meta name="description" content="Lista de regalos para el baby shower de <?= e($ajustes['nombre_bebe']) ?>. Una guía con las cositas que más nos ayudarán como papás primerizos.">
<meta property="og:title" content="<?= e($ajustes['titulo']) ?>">
<meta property="og:description" content="Acompáñanos a celebrar la llegada de <?= e($ajustes['nombre_bebe']) ?>. Esta lista es solo una guía; tu presencia es el mejor regalo.">
<meta property="og:type" content="website">
<meta name="robots" content="index, follow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/styles.css">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='14' fill='%23E8B4C0'/><circle cx='16' cy='16' r='7' fill='%23FDFBF7'/></svg>">
</head>
<body>

<div class="bg-orbs" aria-hidden="true">
    <span class="orb orb-1"></span>
    <span class="orb orb-2"></span>
    <span class="orb orb-3"></span>
</div>

<header class="hero">
    <div class="hero-inner reveal">
        <span class="eyebrow">Baby Shower · Es una niña</span>
        <h1 class="hero-title">
            Un regalito para<br>
            <em><?= e($ajustes['nombre_bebe']) ?></em>
        </h1>
        <div class="hero-divider" aria-hidden="true">
            <svg width="120" height="16" viewBox="0 0 120 16" fill="none">
                <path d="M2 8 H44" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
                <path d="M60 3.5c-2.6-3-7.4-1.7-7.4 1.9 0 2.9 4.2 5.4 7.4 7.1 3.2-1.7 7.4-4.2 7.4-7.1 0-3.6-4.8-4.9-7.4-1.9Z" stroke="currentColor" stroke-width="1.1" fill="none"/>
                <path d="M76 8 H118" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
            </svg>
        </div>
        <p class="hero-message"><?= nl2br(e($ajustes['mensaje'])) ?></p>
        <?php if ($ajustes['fecha_evento'] !== '' || $ajustes['lugar'] !== ''): ?>
        <div class="hero-meta">
            <?php if ($ajustes['fecha_evento'] !== ''): ?>
            <span class="meta-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 9.5h17M8 3v4M16 3v4"/></svg>
                <?= e($ajustes['fecha_evento']) ?>
            </span>
            <?php endif; ?>
            <?php if ($ajustes['lugar'] !== ''): ?>
            <span class="meta-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><path d="M12 21s-7-5.5-7-11a7 7 0 1 1 14 0c0 5.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                <?= e($ajustes['lugar']) ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</header>

<main class="lista" id="lista">
    <?php if ($total === 0): ?>
    <div class="vacio reveal">
        <h2 class="vacio-titulo">La lista se está preparando con mucho amor</h2>
        <p>Muy pronto encontrarás aquí las cositas que más ilusión nos hacen. Vuelve en un ratito.</p>
    </div>
    <?php else: ?>

    <div class="lista-encabezado reveal">
        <div>
            <h2 class="lista-titulo">La lista de regalos</h2>
            <p class="lista-sub">Elige con calma; cada cosita cuenta una historia que apenas empieza.</p>
        </div>
        <div class="progreso" role="status">
            <div class="progreso-barra"><span style="width: <?= $total ? round($elegidos / $total * 100) : 0 ?>%"></span></div>
            <p class="progreso-texto"><strong><?= $elegidos ?></strong> de <?= $total ?> regalitos ya tienen padrino o madrina</p>
        </div>
    </div>

    <div class="filtros reveal" role="tablist" aria-label="Filtrar regalos">
        <button class="filtro activo" data-filtro="todos" role="tab" aria-selected="true">Todos</button>
        <button class="filtro" data-filtro="disponibles" role="tab" aria-selected="false">Disponibles</button>
        <button class="filtro" data-filtro="elegidos" role="tab" aria-selected="false">Ya elegidos</button>
    </div>

    <section class="grid" aria-live="polite">
        <?php foreach ($regalos as $r):
            $enlaces = parsear_enlaces($r['enlaces']);
            $img     = imagen_src($r['imagen']);
            $estado  = $r['elegido'] ? 'elegido' : 'disponible';
        ?>
        <article class="carta reveal <?= $estado ?>" data-estado="<?= $estado ?>" data-id="<?= (int) $r['id'] ?>">
            <div class="carta-marco">
                <figure class="carta-imagen">
                    <?php if ($img !== ''): ?>
                    <img src="<?= e($img) ?>" alt="<?= e($r['nombre']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="imagen-placeholder" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <rect x="4" y="9" width="16" height="11" rx="2.5"/>
                            <path d="M4 13h16M12 9v11M12 9c-4.5 0-5.5-5-2.4-5C11.4 4 12 6.8 12 9Zm0 0c4.5 0 5.5-5 2.4-5C12.6 4 12 6.8 12 9Z"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                    <span class="tag tag-<?= e($r['prioridad']) ?>"><?= e(etiqueta_prioridad($r['prioridad'])) ?></span>
                    <?php if ($r['elegido']): ?>
                    <div class="velo-elegido">
                        <span class="sello">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4.5 12.5 10 18 19.5 7"/></svg>
                            Ya elegido
                        </span>
                    </div>
                    <?php endif; ?>
                </figure>
                <div class="carta-cuerpo">
                    <h3 class="carta-nombre"><?= e($r['nombre']) ?></h3>
                    <?php if (!empty($r['descripcion'])): ?>
                    <p class="carta-desc"><?= nl2br(e($r['descripcion'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['indicaciones'])): ?>
                    <details class="indicaciones">
                        <summary>Indicaciones de los papás</summary>
                        <p><?= nl2br(e($r['indicaciones'])) ?></p>
                    </details>
                    <?php endif; ?>
                    <div class="carta-pie">
                        <?php if (!empty($r['precio_ref'])): ?>
                        <span class="precio">Ref. <?= e($r['precio_ref']) ?></span>
                        <?php endif; ?>
                        <?php if ($enlaces): ?>
                        <div class="enlaces">
                            <?php foreach ($enlaces as $en): ?>
                            <a class="enlace" href="<?= e($en['url']) ?>" target="_blank" rel="noopener noreferrer">
                                <?= e($en['label']) ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M7 17 17 7M9 7h8v8"/></svg>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($r['elegido']): ?>
                    <button class="btn btn-elegido" disabled>
                        Este regalito ya tiene madrina o padrino
                    </button>
                    <?php else: ?>
                    <button class="btn btn-elegir" data-id="<?= (int) $r['id'] ?>" data-nombre="<?= e($r['nombre']) ?>">
                        <span>Quiero regalar esto</span>
                        <span class="btn-icono" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M7 17 17 7M9 7h8v8"/></svg>
                        </span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</main>

<footer class="pie">
    <div class="pie-inner reveal">
        <div class="hero-divider" aria-hidden="true">
            <svg width="120" height="16" viewBox="0 0 120 16" fill="none">
                <path d="M2 8 H44" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
                <path d="M60 3.5c-2.6-3-7.4-1.7-7.4 1.9 0 2.9 4.2 5.4 7.4 7.1 3.2-1.7 7.4-4.2 7.4-7.1 0-3.6-4.8-4.9-7.4-1.9Z" stroke="currentColor" stroke-width="1.1" fill="none"/>
                <path d="M76 8 H118" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
            </svg>
        </div>
        <p class="pie-mensaje">Con o sin regalo, lo que más nos ilusiona es celebrar contigo.<br>Gracias por ser parte de la historia de <em><?= e($ajustes['nombre_bebe']) ?></em>.</p>
        <p class="pie-firma">Con cariño, los futuros papás</p>
    </div>
</footer>

<!-- Modal de elección -->
<div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="modal-titulo" hidden>
    <div class="modal-fondo" data-cerrar></div>
    <div class="modal-caja">
        <button class="modal-cerrar" type="button" data-cerrar aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
        <div class="modal-paso" id="paso-formulario">
            <span class="eyebrow">Elegir regalo</span>
            <h2 id="modal-titulo" class="modal-titulo">Vas a regalar<br><em id="modal-regalo"></em></h2>
            <p class="modal-nota">Solo los papás verán estos datos; para el resto de invitados el regalo aparecerá como “ya elegido”.</p>
            <form id="form-claim" novalidate>
                <input type="hidden" name="gift_id" id="campo-gift-id" value="">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <label class="campo">
                    <span>Tu nombre completo</span>
                    <input type="text" name="nombre" autocomplete="name" maxlength="150" required placeholder="María Fernanda Pérez">
                </label>
                <label class="campo">
                    <span>Cédula</span>
                    <input type="text" name="cedula" inputmode="numeric" autocomplete="off" maxlength="10" required placeholder="0912345678">
                </label>
                <label class="campo">
                    <span>Correo electrónico</span>
                    <input type="email" name="correo" autocomplete="email" maxlength="150" required placeholder="maria@correo.com">
                </label>
                <p class="form-error" id="form-error" role="alert" hidden></p>
                <button type="submit" class="btn btn-elegir btn-modal" id="btn-confirmar">
                    <span>Confirmar mi regalo</span>
                    <span class="btn-icono" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4.5 12.5 10 18 19.5 7"/></svg>
                    </span>
                </button>
            </form>
        </div>
        <div class="modal-paso" id="paso-gracias" hidden>
            <div class="gracias-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1"><path d="M12 21c-4.5-3-9-6.5-9-11a5 5 0 0 1 9-3 5 5 0 0 1 9 3c0 4.5-4.5 8-9 11Z"/></svg>
            </div>
            <h2 class="modal-titulo">¡Gracias de corazón!</h2>
            <p class="modal-nota" id="gracias-mensaje"></p>
            <button type="button" class="btn btn-elegir btn-modal" data-cerrar>
                <span>Volver a la lista</span>
            </button>
        </div>
    </div>
</div>

<script src="assets/js/app.js" defer></script>
</body>
</html>
