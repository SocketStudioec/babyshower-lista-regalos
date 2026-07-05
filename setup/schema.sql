-- Lista de Regalos Baby Shower — esquema de base de datos
-- MySQL 5.7+ / MariaDB 10.3+  ·  utf8mb4

CREATE TABLE IF NOT EXISTS settings (
  id            TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  nombre_bebe   VARCHAR(100) NOT NULL DEFAULT 'Nuestra Bebé',
  titulo        VARCHAR(200) NOT NULL DEFAULT 'Lista de Regalos',
  mensaje       TEXT NULL,
  fecha_evento  VARCHAR(120) NOT NULL DEFAULT '',
  lugar         VARCHAR(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario     VARCHAR(50) NOT NULL UNIQUE,
  clave_hash  VARCHAR(255) NOT NULL,
  creado_en   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gifts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(200) NOT NULL,
  descripcion  TEXT NULL,
  indicaciones TEXT NULL,
  enlaces      TEXT NULL,                -- un enlace por línea
  imagen       VARCHAR(500) NULL,        -- archivo en /uploads o URL externa
  prioridad    ENUM('alta','media','baja') NOT NULL DEFAULT 'media',
  precio_ref   VARCHAR(60) NULL,         -- precio referencial (texto libre)
  orden        INT NOT NULL DEFAULT 0,
  activo       TINYINT(1) NOT NULL DEFAULT 1,
  creado_en    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS claims (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  gift_id    INT UNSIGNED NOT NULL,
  nombre     VARCHAR(150) NOT NULL,
  cedula     VARCHAR(20)  NOT NULL,
  correo     VARCHAR(150) NOT NULL,
  creado_en  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_claims_gift (gift_id),   -- un regalo solo puede elegirse una vez
  CONSTRAINT fk_claims_gift FOREIGN KEY (gift_id) REFERENCES gifts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fila única de ajustes con el mensaje de bienvenida por defecto
INSERT INTO settings (id, nombre_bebe, titulo, mensaje, fecha_evento, lugar)
VALUES (
  1,
  'Nuestra Princesa',
  'Lista de Regalos · Baby Shower',
  'Esta lista es solo una guía con las cositas que, como papás primerizos, nos van a ayudar muchísimo en esta nueva aventura. Si eliges un regalo de aquí, nos haces la vida más fácil; y si no eliges ninguno, igual nos sentimos profundamente felices de que nos acompañes en este día tan especial. Tu presencia es el mejor regalo.',
  '',
  ''
)
ON DUPLICATE KEY UPDATE id = id;
