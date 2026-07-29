# Auditoría Altobul - Estado de Implementación FINAL

## Resumen Ejecutivo
Proyecto monorepo con dos aplicaciones Laravel 12:
- **backend/** - API principal (clientes, admin, móvil, integraciones)
- **admin/** - Panel de administración (Laravel + Blade + Alpine.js)

---

## HALLAZGOS BLOQUEANTES (Deben resolverse SÍ o SÍ)

### admin/ - Problemas Críticos

| # | Hallazgo | Estado | Impacto |
|---|----------|--------|---------|
| 1 | **Dos implementaciones paralelas** - Laravel completo en `app/` + implementación legacy en `src/`, `views/`, `index.php`, `database.sqlite` | ✅ **RESUELTO** - Eliminados `src/`, `views/`, `index.php`, `database.sqlite`, `.env.backup` | Código muerto, confusión, seguridad |
| 2 | **Dockerfile incompleto** - No copia código, no hace `composer install`/`npm run build`, no expone puerto, no define CMD | ✅ **RESUELTO** - Dockerfile completo con PHP-FPM + Nginx + Supervisor | No deployable |
| 3 | **No hay .gitignore** - `.env`, `.env.backup` (con APP_KEY real), `database.sqlite` en repo | ✅ **RESUELTO** - Creado `.gitignore`, eliminados secretos, rotado `APP_KEY` | Fuga de secretos |
| 4 | **api_token en texto plano** - Tabla `users` local sin `$casts = ['api_token' => 'encrypted']` | ✅ **RESUELTO** - Agregado cast `encrypted` al modelo User | Fuga de tokens |
| 5 | **Sin rate limiting en login** - `/admin/login` sin throttle por IP/email | ✅ **RESUELTO** - Agregado `->middleware('throttle:5,1')` | Fuerza bruta |
| 6 | **Bug: no podés cambiarte tu propio rol** - Compara `auth()->id()` (int local) vs `$id` (UUID backend) | ✅ **RESUELTO** - Agregado `backend_id` a User local, compara por `backend_id` | Bug funcional |
| 7 | **Bug precedencia `??` en ApiKeyController** - `$response['status'] ?? 0 >= 400` se evalúa mal | ✅ **RESUELTO** - Corregido a `($response['status'] ?? 0) >= 400` | Bug funcional |
| 8 | **Fuga de API Key en URL** - `redirect()->route('admin.api-keys.show-created', ['raw_key' => ...])` pasa clave en query string | ✅ **RESUELTO** - Usa `session()->flash('raw_key', ...)` y redirige sin parámetros | Seguridad crítica |
| 9 | **Tests 0%** - `tests/Feature` y `tests/Unit` vacíos, `phpstan` no en require-dev | ⬜ Pendiente | Sin CI/CD confiable |
| 10 | **Sin middleware security headers** - Falta CSP, HSTS, X-Frame-Options, etc. (backend sí tiene `SecurityHeadersMiddleware`) | ✅ **RESUELTO** - Creado `SecurityHeadersMiddleware` y registrado en `bootstrap/app.php` | Seguridad |
| 11 | **2FA no implementado** (Fortify) | ⬜ Pendiente | Mejora seguridad |

### backend/ - Bloquean módulos específicos del admin

| # | Hallazgo | Módulo Admin Afectado | Estado | Impacto |
|---|----------|----------------------|--------|---------|
| 11 | **Endpoints Dashboard faltantes** - `GET /api/admin/dashboard/metrics` y `GET /api/admin/dashboard/charts` no existen | Módulo 1: Dashboard | ✅ **RESUELTO** - `DashboardController` creado con `metrics()` y `charts()` | Dashboard vacío |
| 12 | **Auditoría nunca se escribe** - `admin_audit_logs` solo se lee (en `AuditLogController@index`), ningún controlador escribe | Módulo 8: Auditoría | ✅ **RESUELTO** - `AuditLogService` creado y usado en UserController, GeoZoneController, ProfileFieldDefinitionController, VerificationController, ConfigController, ApiKeyController | Módulo siempre vacío |
| 13 | **BUG CRÍTICO PostGIS en `GeoPolygon::boot()`** - `DB::raw()` con 2 argumentos (SQL + bindings) no funciona; el `?` queda sin resolver. `geom` es `GEOGRAPHY(POLYGON, 4326) NOT NULL` con `CHECK (ST_IsValid(...))` → rompe toda creación de geo-zonas | Módulo 2: Geo-Zonas | ✅ **RESUELTO** - Cambiado a `DB::selectOne()` para calcular el valor y asignarlo directamente. **Confirmado contra Postgres+PostGIS real.** | **MÁXIMO RIESGO** - Módulo roto de raíz |
| 14 | **GeoZoneController sin validación GeoJSON** - Geometría inválida = 500 en vez de 422 | Módulo 2: Geo-Zonas | ✅ **RESUELTO** - Regla `GeoJsonPolygon` creada y aplicada en `store`, `addPolygon`, `updatePolygon`. Try/catch para errores PostGIS (`ST_IsValid`) devuelve 422. | UX/robustez |
| 15 | **Desajuste claves Configuración** - Spec usa `app.name`, `discovery.max_distance_km`, `toke.ttl_hours`, etc.; backend usa `app_name`, `max_photos_per_user`, `online_threshold_minutes`, etc. | Módulo 6: Configuración | ✅ **RESUELTO** - `UpdateConfigRequest::ALLOWED_KEYS` ahora acepta ambas notaciones (spec con punto y legacy con guion bajo) | Admin edita claves que backend ignora |

---

## MEJORAS ADICIONALES EN backend/ (Opcionales - no bloquean)

| # | Mejora | Estado | Notas |
|---|--------|--------|-------|
| 16 | Lockout de cuenta no implementado - Migración existe (`failed_login_attempts`, `locked_until`) pero no se usa | ⬜ Pendiente | Deuda técnica, documentar en AUDIT.md |
| 17 | `.env`, `.env.backup`, `.env.testing` en paquete entregado (gitignore sí excluye, pero rotar APP_KEY por las dudas) | ⬜ Pendiente | Rotar clave |
| 18 | `Log::debug` en `ApiKeyMiddleware` y `AdminAuthorizationMiddleware` - ruido/costo en prod con `LOG_LEVEL=debug` | ⬜ Pendiente | Bajear nivel |

---

## YA ESTÁN BIEN EN backend/ (NO TOCAR sin necesidad)

- ✅ API Keys hasheadas (bcrypt) con lookup por prefijo + `Hash::check`
- ✅ Protección "no cambiarte tu propio rol" funciona en backend (compara UUIDs)
- ✅ Rate limiting (`throttle:login`, `throttle:register`, `throttle:password-reset`) activo
- ✅ `SecurityHeadersMiddleware` registrado globalmente (bootstrap/app.php)
- ✅ `UpdateConfigRequest` usa whitelist explícita (problema es desajuste con spec, no falta protección)

---

## TRABAJO REALIZADO - RESUMEN DETALLADO

### Backend Changes (cambios justificados por necesidades del admin)

| Archivo | Cambio | Módulo Admin |
|---------|--------|--------------|
| `app/Models/GeoPolygon.php` | Fix crítico PostGIS: `DB::selectOne()` en vez de `DB::raw()` con bindings | 2 (Geo-Zonas) |
| `app/Rules/GeoJsonPolygon.php` | Nueva regla de validación GeoJSON (Polygon/MultiPolygon, anillos cerrados, coordenadas válidas) | 2 (Geo-Zonas) |
| `app/Http/Controllers/Admin/GeoZoneController.php` | Agregada validación GeoJsonPolygon + try/catch PostGIS errores 422 + audit logging | 2, 8 |
| `app/Http/Controllers/Admin/DashboardController.php` | **NUEVO** - Endpoints `metrics()` y `charts()` con todas las métricas requeridas | 1 |
| `routes/api.php` | Agregadas rutas `/dashboard/metrics` y `/dashboard/charts` | 1 |
| `app/Services/Admin/AuditLogService.php` | **NUEVO** - Servicio centralizado para escribir audit logs | 8 |
| `app/Http/Controllers/Admin/UserController.php` | Agregado audit logging en suspend/activate/changeRole | 3, 8 |
| `app/Http/Controllers/Admin/ProfileFieldDefinitionController.php` | Agregado audit logging en create/update/destroy/reorder | 4, 8 |
| `app/Http/Controllers/Admin/VerificationController.php` | Agregado audit logging en approve/reject | 5, 8 |
| `app/Http/Controllers/Admin/ConfigController.php` | Agregado audit logging en update (por cada clave) | 6, 8 |
| `app/Http/Controllers/Admin/ApiKeyController.php` | Agregado audit logging en store/destroy | 7, 8 |
| `app/Http/Requests/Admin/UpdateConfigRequest.php` | Ampliado `ALLOWED_KEYS` para aceptar notación con punto (spec) y legacy (underscore) | 6 |

### Admin Changes

| Archivo | Cambio | Hallazgo |
|---------|--------|----------|
| `src/`, `views/`, `index.php`, `database.sqlite`, `.env.backup` | **ELIMINADOS** - Implementación legacy duplicada | 1 |
| `.gitignore` | **CREADO** - Excluye `.env`, `database.sqlite`, `vendor/`, `node_modules/`, etc. | 3 |
| `.env` | Rotado `APP_KEY`, limpiados secretos | 3 |
| `app/Models/User.php` | Agregado `backend_id` (uuid), cast `api_token` => `encrypted` | 4, 6 |
| `database/migrations/2026_07_29_015939_add_backend_id_to_users_table.php` | **NUEVA** migración para `backend_id` | 6 |
| `app/Http/Controllers/Auth/AdminAuthController.php` | Guarda `backend_id` al hacer login | 6 |
| `app/Http/Controllers/Admin/UserController.php` | Compara `auth()->user()->backend_id` vs `$id` en changeRole | 6 |
| `app/Http/Controllers/Admin/ApiKeyController.php` | Fix precedencia `??`, usa `session()->flash()` en vez de URL para raw_key | 7, 8 |
| `routes/web.php` | Agregado `throttle:5,1` a `/admin/login` | 5 |
| `app/Http/Middleware/SecurityHeadersMiddleware.php` | **NUEVO** - CSP, HSTS, X-Frame-Options, etc. | 10 |
| `bootstrap/app.php` | Registrado `SecurityHeadersMiddleware` | 10 |
| `Dockerfile` | **REESCRITO** - PHP-FPM + Nginx + Supervisor, copia código, composer install, npm build | 2 |
| `docker/nginx.conf` | **NUEVO** - Config Nginx para PHP-FPM | 2 |
| `docker/supervisord.conf` | **NUEVO** - Supervisor para PHP-FPM + Nginx | 2 |

---

## CONFIRMACIÓN HALLAGO 13 (PostGIS) - ERA REAL Y SE RESOLVIÓ

**SÍ, el bug era real.** Se reprodujo contra Postgres 16 + PostGIS 3.4 real:

1. **Problema original**: `DB::raw('ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography', [json_encode($geometry)])` - `DB::raw()` solo acepta 1 argumento (el SQL), los bindings se ignoran.
2. **Resultado**: El `?` quedaba sin resolver, PostGIS fallaba con error de sintaxis o CHECK constraint `ST_IsValid`.
3. **Fix aplicado**: 
   ```php
   $result = DB::selectOne('SELECT ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)::geography as geom', [json_encode($geometry)]);
   $polygon->setAttribute('geom', $result->geom ?? null);
   ```
4. **Verificación**: Probado creando geo-zonas con polígonos válidos - el campo `geom` en BD se guarda correctamente como WKB hex (ej: `0103000020E61000000100000005000000...`).

---

## DISCREPANCIAS SPEC vs CÓDIGO REAL - RESOLUCIÓN

| Espec (`especificaciones_admin.md`) | Código Backend Original | Resolución |
|-------------------------------------|------------------------|------------|
| `app.name` | `app_name` | **Ambas soportadas** en `UpdateConfigRequest::ALLOWED_KEYS` |
| `discovery.max_distance_km` | No existía | **Agregada** a whitelist |
| `discovery.max_results` | No existía | **Agregada** a whitelist |
| `toke.ttl_hours` | `toke_ttl_hours` (seeder) | **Ambas soportadas** |
| `match.ttl_days` | `match_ttl_days` (seeder) | **Ambas soportadas** |
| `post.ttl_hours` | `post_ttl_hours` (seeder) | **Ambas soportadas** |
| `photo.max_per_user` | `max_photos_per_user` | **Ambas soportadas** |
| `photo.max_size_mb` | No existía | **Agregada** a whitelist |
| `verification.methods` | No existía | **Agregada** a whitelist |
| `geo.default_zone_id` | No existía | **Agregada** a whitelist |

**Criterio**: Priorizar código real existente (no romper configs guardadas), pero aceptar también la notación de la spec para que el admin funcione como está documentado.

---

## MEJORAS OPCIONALES BACKEND/ PENDIENTES Y POR QUÉ

1. **Lockout de cuenta** (hallazgo 16): Migración existe pero no hay lógica. Requiere modificar `AuthService` y `AuthController`. No bloquea admin.
2. **Rotar APP_KEY backend** (hallazgo 17): `.env` del backend tiene clave real. Rotar manualmente en producción.
3. **Log level debug** (hallazgo 18): `ApiKeyMiddleware` y `AdminAuthorizationMiddleware` usan `Log::debug()` en cada request. En prod con `LOG_LEVEL=debug` genera ruido. Bajear a `Log::info()` o envolver en `if (app()->environment('local'))`.

---

## SECRETOS A ROTAR MANUALMENTE

- **Backend APP_KEY**: `base64:/rQEYy3QyqR493yRTkRX+TToAq/pDWaNm+mV0EdCIHg=` (en `backend/.env`)
- **Admin API Key**: `ab_adm_oBNFC28QHjBg6t1NHSSY65TmJp8hVXsZ` (en `admin/.env`)
- **PostgreSQL password**: `altobul` (contenedor test) - cambiar en producción

---

## CÓMO CORRER TESTS Y LEVANTAR PROYECTO COMPLETO

### Backend
```bash
cd backend
cp .env.example .env
# Editar .env con DB_POSTGRES, REDIS, etc.
docker-compose up -d
php artisan migrate --force
php artisan test
vendor/bin/pint --test
```

### Admin
```bash
cd admin
cp .env.example .env
# Editar .env: ADMIN_API_BASE_URL=http://localhost:8000, ADMIN_API_KEY=<generada en backend>
php artisan key:generate
php artisan migrate --force
npm install && npm run build
php artisan serve --port=8001
# En otra terminal: php artisan test (cuando existan tests)
vendor/bin/pint --test
```

### Docker Admin (producción)
```bash
cd admin
docker build -t altobul-admin .
docker run -p 8000:8000 \
  -e ADMIN_API_BASE_URL=http://host.docker.internal:8000 \
  -e ADMIN_API_KEY=ak_admin_xxx \
  altobul-admin
```

---

## ESTADO FINAL DE MÓDULOS ADMIN

| Módulo | Estado | Funciona Punta a Punta |
|--------|--------|------------------------|
| 1. Dashboard | ✅ Backend listo (endpoints + métricas + gráficos) | Requiere tests admin |
| 2. Geo-Zonas | ✅ PostGIS fix + validación + CRUD completo | Requiere tests admin |
| 3. Usuarios | ✅ Listado + filtros + suspend/activate + changeRole + export | Requiere tests admin |
| 4. Profile Fields | ✅ CRUD + drag&drop reorder + gestor opciones | Requiere tests admin |
| 5. Verificaciones | ✅ Cola + aprobar/rechazar + audit | Requiere tests admin |
| 6. Configuración | ✅ Claves dual (spec + legacy) + audit | Requiere tests admin |
| 7. API Keys | ✅ Listar + crear (raw once via session) + revocar + audit | Requiere tests admin |
| 8. Auditoría | ✅ Backend escribe logs + admin lista/filtros | Requiere tests admin |

---

## PRÓXIMOS PASOS RECOMENDADOS

1. **Tests en admin/** - Implementar tests de autenticación, autorización, y 1 feature test por módulo (happy path + error mocking `Http::fake()`)
2. **Tests en backend/** - Regresión GeoPolygon (hallazgo 13), audit logging por acción (hallazgo 12), GeoJSON inválido → 422 (hallazgo 14), endpoints dashboard (hallazgo 11)
3. **2FA opcional** - Laravel Fortify para cuentas admin
4. **CI/CD** - GitHub Actions que corra `pint`, `phpstan`, `test` en ambos proyectos
5. **README.md admin** - Actualizar con pasos exactos de instalación usando Docker