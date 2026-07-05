<?php
/**
 * Configuración de la aplicación — Lista de Regalos Baby Shower
 * Copiar este archivo como config.php y completar los valores reales.
 * config.php NUNCA se sube al repositorio (ver .gitignore).
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'babyshower_regalos');
define('DB_USER', 'CAMBIAR_USUARIO');
define('DB_PASS', 'CAMBIAR_CLAVE');

// Zona horaria del evento
date_default_timezone_set('America/Guayaquil');

// Carpeta física de imágenes subidas por el administrador
define('UPLOADS_DIR', dirname(__DIR__) . '/uploads');

// Tamaño máximo de imagen (bytes) — 4 MB
define('UPLOAD_MAX_BYTES', 4 * 1024 * 1024);
