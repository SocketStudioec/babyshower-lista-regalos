# Lista de Regalos · Baby Shower

Aplicación web sencilla y elegante para gestionar la lista de regalos de un baby shower (temática de niña). Los papás cargan los regalos desde un panel privado; los invitados entran a un enlace público, ven la lista y eligen qué regalar.

## Características

**Página pública**
- Mensaje de bienvenida cálido que aclara que la lista es solo una guía y que la asistencia es lo más importante.
- Tarjetas de regalo con imagen, descripción, indicaciones de los papás, prioridad, precio referencial y enlaces de compra.
- Elección de regalo con nombre, cédula y correo (datos visibles solo para los administradores).
- Los regalos elegidos se marcan como "Ya elegido" sin revelar quién los eligió.
- Barra de progreso, filtros (todos / disponibles / elegidos) y diseño responsive.
- Protección contra elecciones simultáneas (bloqueo de fila + restricción UNIQUE).

**Panel de administración** (`/admin`)
- Login con contraseña cifrada (bcrypt) y freno anti fuerza bruta.
- CRUD de regalos: nombre, descripción, indicaciones, enlaces (uno por línea), imagen (subida o URL), prioridad, precio referencial, orden y visibilidad.
- Vista de elecciones con nombre, cédula, correo y fecha de cada invitado.
- Liberar un regalo (si un invitado se retracta) y ajustes del evento (nombre de la bebé, mensaje, fecha, lugar).

## Stack

- **Backend:** PHP 8 (sin frameworks — sube directo a cualquier hosting con PHP), PDO + MySQL.
- **Frontend:** HTML semántico + CSS artesanal (Fraunces + Plus Jakarta Sans) + JavaScript vanilla.
- **Seguridad:** consultas preparadas, tokens CSRF, `password_hash`, sesiones endurecidas, validación de subidas, `/uploads` sin ejecución de PHP.

## Instalación

1. Crear la base de datos y tablas: `mysql -u root -p < setup/schema.sql` (dentro de una BD `babyshower_regalos`).
2. Crear un usuario MySQL con permisos solo sobre esa base.
3. Copiar `includes/config.sample.php` a `includes/config.php` y completar credenciales.
4. Crear el administrador:
   ```bash
   php -r "echo password_hash('TU_CLAVE', PASSWORD_DEFAULT), PHP_EOL;"
   # luego en MySQL:
   # INSERT INTO admins (usuario, clave_hash) VALUES ('admin', '<hash>');
   ```
5. Subir la carpeta al hosting y dar permisos de escritura a `uploads/` (usuario del servidor web).

## Estructura

```
├── index.php              # Lista pública
├── api/claim.php          # Endpoint para elegir regalo
├── admin/                 # Panel privado (login, CRUD, elecciones, ajustes)
├── includes/              # Config, conexión PDO, helpers, auth
├── assets/css | js        # Estilos y comportamiento
├── uploads/               # Imágenes subidas (protegido)
└── setup/schema.sql       # Esquema de base de datos
```

## Privacidad

Los datos personales de los invitados (nombre, cédula, correo) se almacenan únicamente para coordinar los regalos y solo son visibles en el panel de administración. Se recomienda eliminarlos después del evento.
