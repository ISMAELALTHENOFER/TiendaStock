<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## TiendaStock — Operations Runbook

### Setup (first run, after clone)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate            # creates tables; idempotent
php artisan storage:link       # creates public/storage -> storage/app/public symlink (HTTP-serving for product images)
npm install && npm run build
```

### Post-deploy checklist

| Step | Command | Why |
|------|---------|-----|
| Apply schema changes | `php artisan migrate` | Adds the nullable `imagen` column to `productos` (no data backfill). |
| Public storage symlink | `php artisan storage:link` | Enables `Storage::url('productos/...')` to resolve to a web-reachable URL. Without it, product image thumbnails in index/show render a broken link. |

### Módulo Productos — comportamiento operativo

- **Desactivar vs. eliminar**: `destroy` setea `activo = false`; el registro NO se borra (preserva el histórico de ventas). Los productos inactivos no aparecen en POS (`productos.data` ni `productos.search`) por defecto. En el índice, el toggle *Ver inactivos* los incluye para gestión.
- **Reactivar**: ejecutar `UPDATE productos SET activo = 1 WHERE activo = 0` (o editar el producto — al guardar sin imagen nueva se conserva el path existente).
- **Imágenes**: se guardan en el disco `public` bajo `productos/` con un nombre generado (nunca el nombre del cliente). `max:2048` KB; mime `jpg,jpeg,png,webp`.
- **Dinero**: el input visible muestra `$1.234,56` (Alpine mask); un input hidden envía el float numérico al backend. La validación `numeric` del Form Request rechaza cualquier string formateado adulterado (defense-in-depth → 422).

### Rollback (revert `productos-ux-fixes`)

```bash
php artisan migrate:rollback     # drops the nullable `imagen` column
# Restaurar controladores/vistas/routes/Form Requests vía git revert del PR.
# Opcional (solo si se revirtió el soft-disable): reactivar todos los productos
#   UPDATE productos SET activo = 1 WHERE activo = 0;
```

### Tests

```bash
php artisan test                 # suite completa (Unit + Feature)
composer test                   # alias con config:clear previo
```
