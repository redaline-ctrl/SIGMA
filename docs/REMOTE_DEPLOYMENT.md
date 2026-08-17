# Publicar SIGMA

La aplicacion puede generar enlaces con una URL publica mediante `SIGMA_PUBLIC_URL`, pero el acceso desde cualquier lugar requiere publicar el servidor web y la base de datos en una infraestructura accesible.

## Despliegue recomendado

1. Usar un servidor con Apache/Nginx, PHP 8.3+, `pdo_mysql`, `fileinfo`, `zip` y HTTPS.
2. Copiar el proyecto fuera de un recurso local de desarrollo y apuntar el document root a `public/`.
3. Crear la base MySQL y ejecutar las migraciones de `database/migrations/`.
4. Configurar las variables de `.env.example` en el entorno del servidor. No publicar el archivo `.env` ni credenciales.
5. Definir `SIGMA_PUBLIC_URL=https://tu-dominio.example`.
6. Configurar el servidor para que solo `public/` sea accesible por HTTP; `config/`, `app/`, `storage/` y `database/` deben quedar fuera del document root o bloqueados.
7. Habilitar HTTPS, firewall, copias de seguridad y un usuario MySQL con privilegios mínimos.

## Acceso temporal desde otra red

Para una prueba interna se puede usar una VPN como Tailscale o publicar temporalmente el puerto con un túnel HTTPS. No se recomienda exponer directamente el servidor de desarrollo de Laragon a Internet.

## Roles

- `administrador`: control completo.
- `supervisor`: operación y mantenimiento.
- `monitorista`: crear/importar eventos, crear relevos y exportar reportes.
- `gerente`: dashboard, reportes e historial en modo consulta.
- `rh`: dashboard, reportes e historial en modo consulta.
