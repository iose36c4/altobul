# Especificaciones Técnicas - Altobul Admin Web App

## 1. Resumen Ejecutivo

Aplicación web de administración desarrollada en **PHP (Laravel)** que consume la **API de administración** del backend Altobul (Laravel 12 + PostgreSQL + PostGIS). La aplicación proporciona una interfaz web segura para gestionar completamente la plataforma de encuentros.

### Stack Tecnológico Recomendado
- **Backend Admin**: Laravel 12 (PHP 8.3) - mismo stack que backend API
- **Frontend**: Blade + Alpine.js + Tailwind CSS (consistente con Laravel)
- **Mapas**: Leaflet.js + Leaflet.draw (PostGIS GeoJSON)
- **Autenticación Web**: Laravel Sanctum (session-based) + AdminWebGuardMiddleware
- **API Client**: HTTP Client nativo de Laravel (Http::withToken())

---

## 2. Arquitectura y Seguridad

### 2.1 Autenticación y Autorización Dual

La aplicación admin tiene **dos capas de autenticación**:

| Capa | Mecanismo | Uso |
|------|-----------|-----|
| **Web (Admin Panel)** | Session + Sanctum + `AdminWebGuardMiddleware` | Login web, dashboard, gestión de API Keys |
| **API (Admin API)** | `X-API-Key: ADMIN` + `Authorization: Bearer <user_token>` | Todas las llamadas API al backend |

#### Flujo de Autenticación Web (Admin Panel)
```
1. GET /admin/login → Formulario login
2. POST /admin/login → Validación credenciales + isAdmin() + status=active
3. Sesión Sanctum creada → Redirect /admin/dashboard
4. Middleware AdminWebGuardMiddleware protege todas las rutas /admin/*
```

#### Flujo de Llamadas API (Admin API)
```php
// En cada request HTTP del panel admin al backend API:
Http::withHeaders([
    'X-API-Key' => config('admin.api_key'),  // ADMIN API Key configurada
    'Authorization' => 'Bearer ' . auth()->user()->api_token,  // Token usuario admin
    'Accept' => 'application/json',
])->get('https://api.altobul.com/api/admin/users');
```

### 2.2 Middlewares de Seguridad (Backend - Ya Implementados)

| Middleware | Ruta | Función |
|------------|------|---------|
| `api.key:ADMIN` | `/api/admin/*` | Valida X-API-Key tipo ADMIN |
| `auth:sanctum` | `/api/admin/*` | Valida Bearer token usuario |
| `admin` (AdminAuthorizationMiddleware) | `/api/admin/*` (protegidas) | Verifica `user->isAdmin()` |

### 2.3 Configuración Requerida en Admin App (.env)

```env
# Backend API
ADMIN_API_BASE_URL=https://api.altobul.com/api
ADMIN_API_KEY=ak_admin_xxxxxxxxxxxxx  # Generada vía /admin/api-keys/create

# App
APP_URL=https://admin.altobul.com
SESSION_DOMAIN=.altobul.com
SANCTUM_STATEFUL_DOMAINS=admin.altobul.com
```

---

## 3. Módulos Funcionales

### 3.1 Módulo 1: Dashboard y Métricas

#### Endpoints API Requeridos (NUEVOS - Backend)
```php
// routes/api.php - Agregar en admin middleware group
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('dashboard/metrics', [DashboardController::class, 'metrics']);
    Route::get('dashboard/charts', [DashboardController::class, 'charts']);
});
```

#### Métricas Requeridas (Backend debe implementar)

| Métrica | Descripción | Query Sugerida |
|---------|-------------|----------------|
| `users.total` | Total usuarios registrados | `User::count()` |
| `users.active` | Usuarios activos (status=active) | `User::where('status','active')->count()` |
| `users.new_24h` | Nuevos últimos 24h | `User::where('created_at', '>', now()->subDay())->count()` |
| `users.new_7d` | Nuevos últimos 7 días | `User::where('created_at', '>', now()->subWeek())->count()` |
| `users.by_role` | Distribución por rol | `User::select('role', DB::raw('count(*) as count'))->groupBy('role')->get()` |
| `users.by_verification` | Por estado verificación | `User::select('verification_status', DB::raw('count(*)'))->groupBy('verification_status')` |
| `matches.active` | Matches activos (7 días) | `UserMatch::active()->count()` |
| `friendships.active` | Amistades activas | `Friendship::active()->count()` |
| `tokes.active` | Tokes activos (48h) | `Toke::where('status','ACTIVE')->where('expires_at','>',now())->count()` |
| `posts.active` | Posts activos (24h) | `Post::active()->count()` |
| `photos.active` | Fotos activas | `Photo::active()->count()` |
| `verification.pending` | Verificaciones pendientes | `VerificationRequest::pending()->count()` |
| `geo.zones.active` | Zonas geográficas activas | `GeoZone::active()->count()` |
| `api_keys.active` | API Keys activas | `ApiKey::where('revoked_at',null)->where(function($q){$q->whereNull('expires_at')->orWhere('expires_at','>',now());})->count()` |

#### Gráficos (Últimos 30 días)
- Registros diarios de usuarios
- Matches creados por día
- Tokes enviados/consumidos por día
- Posts creados por día
- Verificaciones solicitadas/aprobadas/rechazadas

#### Vista Dashboard (Admin Panel)
```
┌─────────────────────────────────────────────────────────────┐
│  ALTOBUL ADMIN DASHBOARD                                    │
├─────────────────────────────────────────────────────────────┤
│  [KPI Cards Row]                                            │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│  │ Usuarios │ │ Activos  │ │ Nuevos   │ │ Verif.   │        │
│  │ Total    │ │ Hoy      │ │ 24h      │ │ Pendientes│        │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘        │
├─────────────────────────────────────────────────────────────┤
│  [Gráfico: Registros últimos 30 días]  [Gráfico: Matches]   │
├─────────────────────────────────────────────────────────────┤
│  [Tabla: Top 10 usuarios más activos] [Actividad reciente]  │
└─────────────────────────────────────────────────────────────┘
```

---

### 3.2 Módulo 2: Área de Servicio (Geo-Zonas)

#### Endpoints API Existentes (Backend)
```
GET    /api/admin/geo-zones              # Listar zonas (paginado)
POST   /api/admin/geo-zones              # Crear zona + polígonos
GET    /api/admin/geo-zones/{zone}       # Ver zona + polígonos
PUT    /api/admin/geo-zones/{zone}       # Actualizar zona
DELETE /api/admin/geo-zones/{zone}       # Eliminar zona
POST   /api/admin/geo-zones/{zone}/polygons           # Añadir polígono
PUT    /api/admin/geo-zones/{zone}/polygons/{polygon} # Editar polígono
DELETE /api/admin/geo-zones/{zone}/polygons/{polygon} # Eliminar polígono
```

#### Modelo de Datos (Backend)
```php
// GeoZone
id (uuid), name, description, is_active, created_by, created_at

// GeoPolygon
id (uuid), zone_id, name, geometry (GeoJSON), sort_order, geom (geography PostGIS)
```

#### Funcionalidades Requeridas

| Función | Descripción |
|---------|-------------|
| **Listado** | Tabla paginada: Nombre, Descripción, Activa, Polígonos, Creado por, Acciones |
| **Crear/Editar Zona** | Formulario + **Mapa interactivo Leaflet** para dibujar polígonos |
| **Gestión Polígonos** | Añadir/Editar/Eliminar múltiples polígonos por zona (agujeros, islas) |
| **Validación GeoJSON** | Validar geometría válida (PostGIS ST_IsValid) antes de guardar |
| **Vista Mapa** | Mapa full-screen con todas las zonas activas superpuestas |
| **Toggle Activa** | Activar/Desactivar zona (afecta descubrimiento usuarios) |

#### Implementación Mapa (Frontend)
```javascript
// resources/js/admin/geo-zones/map.js
import L from 'leaflet';
import 'leaflet-draw';

const map = L.map('map').setView([-34.6037, -58.3816], 10); // Default Buenos Aires
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

const drawnItems = new L.FeatureGroup();
map.addLayer(drawnItems);

const drawControl = new L.Control.Draw({
    edit: { featureGroup: drawnItems },
    draw: {
        polygon: { allowIntersection: false, showArea: true },
        polyline: false, circle: false, rectangle: false, marker: false, circlemarker: false
    }
});
map.addControl(drawControl);

map.on(L.Draw.Event.CREATED, (e) => {
    const layer = e.layer;
    drawnItems.addLayer(layer);
    const geojson = layer.toGeoJSON();
    // Enviar geojson.geometry al backend via hidden input
});
```

#### Validación Backend (GeoZoneController::store)
```php
'polygons.*.geometry' => ['required', 'array', function ($attr, $value, $fail) {
    // Validar GeoJSON Polygon/MultiPolygon válido
    if (!isset($value['type']) || !in_array($value['type'], ['Polygon', 'MultiPolygon'])) {
        $fail('Geometría debe ser Polygon o MultiPolygon');
    }
    if (!isset($value['coordinates']) || !is_array($value['coordinates'])) {
        $fail('Coordenadas requeridas');
    }
    // Validar anillos cerrados, etc.
}],
```

---

### 3.3 Módulo 3: Gestión Completa de Usuarios

#### Endpoints API Existentes
```
GET  /api/admin/users                    # Listar (filtros: search, role, status, verification_status)
GET  /api/admin/users/{user}             # Ver detalle completo
POST /api/admin/users/{user}/suspend     # Suspender usuario
POST /api/admin/users/{user}/activate    # Activar usuario
POST /api/admin/users/{user}/change-role # Cambiar rol (user/admin)
```

#### Modelo Usuario (Backend)
```php
// User
id (uuid), email, password_hash, role (user|admin), status (active|suspended),
verification_status (unverified|pending|verified), verified_at,
email_verified_at, last_seen_at, failed_login_attempts, locked_until

// Profile (1:1)
user_id, title, description, birth_date, profile_visibility, profile_requires_verified,
title_visibility, description_visibility, birth_date_visibility,
location_precision_meters, location (Point), discoverable, geo_zone_id

// Photos (1:N)
id, user_id, storage_key, mime_type, width, height, size_bytes,
sort_order, is_primary, visibility, requires_verified, status, deleted_at

// Posts (1:N)
id, user_id, content_md, content_html, visibility, requires_verified,
expires_at, status, deleted_at

// VerificationRequest (1:N)
id, user_id, status, reviewed_at, reviewed_by, rejection_reason,
verification_method, external_reference, submitted_at
```

#### Funcionalidades Requeridas

| Función | Endpoint | Detalle |
|---------|----------|---------|
| **Listado Avanzado** | `GET /users` | Filtros: búsqueda (email/id), rol, status, verificación. Paginación 20/50/100. Orden: creado, último acceso, email |
| **Ver Perfil Completo** | `GET /users/{id}` | Tabs: Datos, Perfil, Fotos, Posts, Tokes, Matches, Amistades, Bloqueos, Verificación, Auditoría |
| **Suspender/Activar** | `POST /suspend|activate` | Soft suspend (status=suspended). Log en audit_logs |
| **Cambiar Rol** | `POST /change-role` | Solo admins pueden crear otros admins. No auto-cambio |
| **Ver Fotos** | - | Grid con thumbnails, visibilidad, estado. Link a S3 signed URL |
| **Ver Posts** | - | Lista con contenido, visibilidad, expiración, adjuntos |
| **Historial Actividad** | - | Últimos 50: login, toke enviado, match, amistad, post, foto |
| **Exportar CSV** | - | Exportar listado filtrado |

#### Vista Detalle Usuario (Tabs)
```
┌────────────────────────────────────────────────────────────┐
│ Usuario: juan@email.com (ID: uuid) [Activo] [Verificado]   │
├──────┬──────┬──────┬──────┬──────┬────────┬────────┬───────┤
│Datos │Perfil│Fotos │Posts │Tokes │Matches │Amigos  │Bloques│
├──────┼──────┼──────┼──────┼──────┼────────┼────────┼───────┤
│      │      │      │      │      │        │        │       │
│Email │Título│Grid  │Lista │Enviados│Activos │Activas │Lista  │
│Rol   │Desc. │Thumb │MD    │Recibidos│Hist.  │Pendientes     │
│Status│Nac.  │Visib.│Visib.│Estado  │Conv.   │        │       │
│Verif.│Loc.  │Prim. │Expir.│        │        │        │       │
│Última│Disc. │      │Adj.  │        │        │        │       │
│Sesión│      │      │      │        │        │        │       │
└──────┴──────┴──────┴──────┴──────┴────────┴────────┴───────┘
```

#### Acciones Bulk (Checkboxes en listado)
- Suspender seleccionados
- Activar seleccionados
- Exportar seleccionados CSV

---

### 3.4 Módulo 4: Gestión de Campos de Metadatos (Profile Fields)

#### Endpoints API Existentes
```
GET    /api/admin/profile-fields              # Listar (ordenados)
POST   /api/admin/profile-fields              # Crear
GET    /api/admin/profile-fields/{field}      # Ver
PUT    /api/admin/profile-fields/{field}      # Actualizar
DELETE /api/admin/profile-fields/{field}      # Eliminar
POST   /api/admin/profile-fields/reorder      # Reordenar (ids[])
```

#### Modelo ProfileFieldDefinition (Backend)
```php
id (uuid), slug, label, description, type, validation_rules (json),
is_active, is_required, is_filterable,
default_visibility (PUBLIC|MATCH|FRIENDS|PRIVATE),
default_requires_verified (bool), sort_order

// ProfileFieldOption (1:N para select/radio/checkbox)
id, field_id, label, value, sort_order, is_active
```

#### Tipos de Campo Soportados (Backend)
| Tipo | Descripción | Validación | Opciones |
|------|-------------|------------|----------|
| `text` | Texto corto | max, min, regex | - |
| `textarea` | Texto largo | max, min | - |
| `number` | Numérico | min, max, integer | - |
| `select` | Dropdown única | required | options[] |
| `multiselect` | Múltiple selección | required, min, max | options[] |
| `radio` | Radio buttons | required | options[] |
| `checkbox` | Checkbox único | boolean | - |
| `date` | Fecha | before, after, date_format | - |
| `boolean` | Sí/No | - | - |

#### Funcionalidades Requeridas

| Función | Descripción |
|---------|-------------|
| **Listado Drag & Drop** | Tabla ordenable (sort_order), toggle activo, editar, eliminar |
| **Crear/Editar Modal** | Formulario dinámico según tipo: text/textarea/number/date → campos simples; select/multiselect/radio → gestor de opciones |
| **Gestor de Opciones** | Para select/multiselect/radio: añadir/editar/eliminar/reordenar opciones (label, value, activo) |
| **Validación JSON** | Editor JSON con validación schema para `validation_rules` |
| **Previsualización** | Vista previa del campo en formulario de usuario |
| **Reordenar Global** | Botón "Reordenar" → modal drag&drop → POST /reorder |

#### Formulario Crear/Editar Campo
```
┌─────────────────────────────────────────────────────────────┐
│ Nuevo Campo de Perfil                                       │
├─────────────────────────────────────────────────────────────┤
│ Slug *          [________________]  (unique, slug)         │
│ Etiqueta *      [________________]                          │
│ Descripción     [________________]                          │
│ Tipo *          [select ▼]  → Cambia campos abajo          │
│ ┌─ Si select/multiselect/radio ─────────────────────────┐  │
│ │ Opciones: [+ Añadir]                                  │  │
│ │ ┌────┬──────────┬──────────┬──────┬───────┐           │  │
│ │ │ #  │ Label    │ Value    │ Orden│ Activo│ Acciones │  │
│ │ ├────┼──────────┼──────────┼──────┼───────┤           │  │
│ │ │ 1  │ Masculino│ male     │ 0    │ ✓     │ 🗑 ↑ ↓   │  │
│ │ │ 2  │ Femenino │ female   │ 1    │ ✓     │ 🗑 ↑ ↓   │  │
│ │ └────┴──────────┴──────────┴──────┴───────┘           │  │
│ └──────────────────────────────────────────────────────┘  │
│ Requerido       [☐]                                       │
│ Filtrble        [☐]  (aparece en filtros descubrimiento)  │
│ Activo          [☑]                                       │
│ Visibilidad Def [PUBLIC ▼]  (PUBLIC|MATCH|FRIENDS|PRIVATE)│
│ Requiere Verif. [☐]  (solo si visibilidad != PUBLIC)      │
│ Reglas Validación (JSON)                                  │
│ ┌─────────────────────────────────────────────────────┐   │
│ │ {"max": 100, "min": 2, "regex": "^[a-zA-Z ]+$"}     │   │
│ └─────────────────────────────────────────────────────┘   │
│ [Cancelar] [Guardar]                                      │
└─────────────────────────────────────────────────────────────┘
```

---

### 3.5 Módulo 5: Verificación de Usuarios

#### Endpoints API Existentes
```
GET /api/admin/verification-requests?status=PENDING|APPROVED|REJECTED
GET /api/admin/verification-requests/{id}
POST /api/admin/verification-requests/{id}/approve
POST /api/admin/verification-requests/{id}/reject  (body: rejection_reason)
```

#### Funcionalidades
| Función | Descripción |
|---------|-------------|
| **Cola Pendientes** | Lista paginada, orden: más antiguas primero |
| **Ver Detalle** | Usuario, email, perfil, método, referencia externa, fecha |
| **Aprobar** | Cambia user.verification_status=verified, verified_at=now |
| **Rechazar** | Requiere motivo (max 500 chars). Cambia status=rejected |
| **Filtros** | Por estado, método, fecha rango |
| **Stats** | Contadores: pendientes, aprobadas hoy, rechazadas hoy |

---

### 3.6 Módulo 6: Configuración del Sistema

#### Endpoints API Existentes
```
GET /api/admin/config        # Obtener todas las configs
PUT /api/admin/config        # Actualizar múltiples (body: {key: value})
```

#### Model AppConfig (Backend)
```php
key (string PK), value (jsonb), description, updated_by, updated_at
```

#### Configuraciones Gestionables

| Key | Tipo | Descripción | Ejemplo |
|-----|------|-------------|---------|
| `app.name` | string | Nombre app | "Altobul" |
| `app.online_threshold_minutes` | int | Minutos para "online" | 2 |
| `discovery.max_distance_km` | int | Radio búsqueda km | 50 |
| `discovery.max_results` | int | Límite resultados | 50 |
| `toke.ttl_hours` | int | TTL Tokes (horas) | 48 |
| `match.ttl_days` | int | TTL Match (días) | 7 |
| `post.ttl_hours` | int | TTL Posts (horas) | 24 |
| `photo.max_per_user` | int | Max fotos usuario | 10 |
| `photo.max_size_mb` | int | Max MB por foto | 10 |
| `verification.methods` | array | Métodos permitidos | `["email", "phone", "id_document"]` |
| `geo.default_zone_id` | uuid | Zona por defecto | uuid |

#### Vista Configuración
- Tabla clave/valor con edición inline
- Validación por tipo (JSON editor para arrays/objects)
- Botón "Restablecer por defecto" por clave
- Auditoría: quién cambió qué y cuándo (audit_logs)

---

### 3.7 Módulo 7: Gestión de API Keys

#### Endpoints API Existentes
```
GET  /api/admin/api-keys              # Listar
POST /api/admin/api-keys              # Crear (body: name, type, expires_in_days)
GET  /api/admin/api-keys/{id}         # Ver
DELETE /api/admin/api-keys/{id}       # Revocar
```

#### Tipos de API Key
| Tipo | Uso | Permisos |
|------|-----|----------|
| `CLIENT` | App cliente (React Native, Web) | Endpoints `/api/client/*` |
| `ADMIN` | App admin (esta app) | Endpoints `/api/admin/*` |
| `MOBILE` | App móvil nativa | Subset client + push |
| `INTEGRATION` | Webhooks, integraciones 3rd party | Personalizado |

#### Funcionalidades
- **Crear**: Nombre, Tipo, Expiración (opcional, max 10 años)
- **Mostrar Raw Key**: **SOLO UNA VEZ** al crear (copy to clipboard)
- **Listado**: Nombre, Tipo, Prefijo (primeros 8), Creado por, Creado, Expira, Último uso, Estado
- **Revocar**: Soft delete (revoked_at), invalida inmediatamente
- **Auditoría**: Cada uso logueado en admin_audit_logs

#### Vista Web (Ya Existe Parcialmente en Backend)
```
/admin/api-keys/create  → Formulario crear
/admin/api-keys/created → Muestra raw key (solo una vez)
/admin                  → Lista con botón revocar
```

---

### 3.8 Módulo 8: Auditoría y Logs

#### Endpoint API Existente
```
GET /api/admin/audit-logs?action=&target_type=&admin_id=&per_page=50
```

#### Modelo AdminAuditLog
```php
id (uuid), admin_id (fk users), action (string), target_type, target_id (uuid),
metadata (jsonb), ip_address, user_agent, created_at
```

#### Acciones Auditadas (Backend - Automáticas via Observers/Events)
| Acción | Target Type | Metadata |
|--------|-------------|----------|
| `user.suspend` | User | `{reason?}` |
| `user.activate` | User | `{}` |
| `user.role_change` | User | `{old_role, new_role}` |
| `geo_zone.create` | GeoZone | `{zone_id, polygons_count}` |
| `geo_zone.update` | GeoZone | `{changes}` |
| `geo_zone.delete` | GeoZone | `{}` |
| `profile_field.create` | ProfileField | `{field_id, type}` |
| `profile_field.update` | ProfileField | `{changes}` |
| `profile_field.delete` | ProfileField | `{}` |
| `verification.approve` | VerificationRequest | `{request_id, user_id}` |
| `verification.reject` | VerificationRequest | `{request_id, user_id, reason}` |
| `config.update` | AppConfig | `{key, old_value, new_value}` |
| `api_key.create` | ApiKey | `{key_id, type}` |
| `api_key.revoke` | ApiKey | `{key_id}` |

#### Vista Auditoría
- Tabla paginada, filtros: acción, target_type, admin, fecha desde/hasta
- Detalle modal: metadata JSON formateado, IP, User-Agent
- Export CSV filtrado

---

## 4. Implementación Técnica (Laravel Admin App)

### 4.1 Estructura de Carpetas
```
admin-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── GeoZoneController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── ProfileFieldController.php
│   │   │   │   ├── VerificationController.php
│   │   │   │   ├── ConfigController.php
│   │   │   │   ├── ApiKeyController.php
│   │   │   │   └── AuditLogController.php
│   │   │   └── Auth/
│   │   │       └── AdminAuthController.php  (login web)
│   │   ├── Middleware/
│   │   │   └── AdminApiClient.php           # Inyecta X-API-Key + Bearer
│   │   └── Requests/
│   │       └── Admin/
│   ├── Services/
│   │   └── BackendApiService.php            # Wrapper HTTP Client
│   └── View/Components/
│       └── Admin/                           # Blade components
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   │   ├── layouts/
│   │   │   │   └── app.blade.php
│   │   │   ├── dashboard/
│   │   │   │   └── index.blade.php
│   │   │   ├── geo-zones/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── create.blade.php
│   │   │   │   ├── edit.blade.php
│   │   │   │   └── partials/
│   │   │   │       └── map.blade.php
│   │   │   ├── users/
│   │   │   │   ├── index.blade.php
│   │   │   │   └── show.blade.php
│   │   │   ├── profile-fields/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── create.blade.php
│   │   │   │   └── edit.blade.php
│   │   │   ├── verifications/
│   │   │   │   ├── index.blade.php
│   │   │   │   └── show.blade.php
│   │   │   ├── config/
│   │   │   │   └── index.blade.php
│   │   │   ├── api-keys/
│   │   │   │   ├── index.blade.php
│   │   │   │   └── create.blade.php
│   │   │   └── audit-logs/
│   │   │       └── index.blade.php
│   │   └── auth/
│   │       └── admin-login.blade.php
│   ├── js/
│   │   └── admin/
│   │       ├── app.js
│   │       ├── geo-zones-map.js
│   │       ├── profile-fields-form.js
│   │       └── sortable-table.js
│   └── css/
│       └── admin.css
├── routes/
│   └── web.php
└── config/
    └── admin.php
```

### 4.2 Servicio Cliente API Backend

```php
// app/Services/BackendApiService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class BackendApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $userToken;

    public function __construct()
    {
        $this->baseUrl = config('admin.api_base_url') . '/api/admin';
        $this->apiKey = config('admin.api_key');
    }

    public function withUserToken(string $token): self
    {
        $this->userToken = $token;
        return $this;
    }

    protected function headers(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'Authorization' => 'Bearer ' . ($this->userToken ?? auth()->user()->api_token),
            'Accept' => 'application/json',
        ];
    }

    public function get(string $endpoint, array $params = []): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(30)
            ->get($this->baseUrl . $endpoint, $params);
    }

    public function post(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(30)
            ->post($this->baseUrl . $endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(30)
            ->put($this->baseUrl . $endpoint, $data);
    }

    public function delete(string $endpoint): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(30)
            ->delete($this->baseUrl . $endpoint);
    }

    // Métodos de conveniencia
    public function getDashboardMetrics(): array { ... }
    public function getUsers(array $filters = []): array { ... }
    public function getUser(string $id): array { ... }
    public function suspendUser(string $id): array { ... }
    public function activateUser(string $id): array { ... }
    public function changeUserRole(string $id, string $role): array { ... }
    public function getGeoZones(array $params = []): array { ... }
    public function createGeoZone(array $data): array { ... }
    public function updateGeoZone(string $id, array $data): array { ... }
    public function deleteGeoZone(string $id): array { ... }
    public function addPolygon(string $zoneId, array $data): array { ... }
    public function updatePolygon(string $zoneId, string $polygonId, array $data): array { ... }
    public function deletePolygon(string $zoneId, string $polygonId): array { ... }
    public function getProfileFields(): array { ... }
    public function createProfileField(array $data): array { ... }
    public function updateProfileField(string $id, array $data): array { ... }
    public function deleteProfileField(string $id): array { ... }
    public function reorderProfileFields(array $ids): array { ... }
    public function getVerificationRequests(array $params = []): array { ... }
    public function getVerificationRequest(string $id): array { ... }
    public function approveVerification(string $id): array { ... }
    public function rejectVerification(string $id, string $reason): array { ... }
    public function getConfig(): array { ... }
    public function updateConfig(array $config): array { ... }
    public function getApiKeys(array $params = []): array { ... }
    public function createApiKey(array $data): array { ... }
    public function revokeApiKey(string $id): array { ... }
    public function getAuditLogs(array $params = []): array { ... }
}
```

### 4.3 Middleware Inyección Token Usuario

```php
// app/Http/Middleware/InjectAdminApiToken.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectAdminApiToken
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->api_token) {
            // Compartir con el service container
            app(BackendApiService::class)->withUserToken(auth()->user()->api_token);
        }
        return $next($request);
    }
}
```

Registrar en `Kernel.php` web middleware group.

### 4.4 Controlador Base Admin

```php
// app/Http/Controllers/Admin/BaseAdminController.php
namespace App\Http\Controllers\Admin;

use App\Services\BackendApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

abstract class BaseAdminController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\AdminWebGuardMiddleware::class);
    }

    protected function apiError(Response $response, string $default = 'Error en la operación'): RedirectResponse
    {
        $message = $response->json('message') ?? $default;
        if ($response->status() === 422) {
            $errors = $response->json('errors') ?? [];
            return back()->withErrors($errors)->withInput()->with('error', $message);
        }
        return back()->with('error', $message);
    }

    protected function apiSuccess(string $message = 'Operación exitosa'): RedirectResponse
    {
        return back()->with('success', $message);
    }
}
```

### 4.5 Ejemplo Controlador GeoZones

```php
// app/Http/Controllers/Admin/GeoZoneController.php
namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreGeoZoneRequest;
use App\Http\Requests\Admin\UpdateGeoZoneRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia; // o View si Blade

class GeoZoneController extends BaseAdminController
{
    public function index(): View
    {
        $response = $this->api->getGeoZones(request()->only(['page', 'per_page']));
        if (! $response->ok()) return $this->apiError($response);
        
        return view('admin.geo-zones.index', [
            'zones' => $response->json('zones'),
            'pagination' => $response->json('pagination'),
        ]);
    }

    public function create(): View
    {
        return view('admin.geo-zones.create');
    }

    public function store(StoreGeoZoneRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // polygons viene como array de {name, geometry, sort_order}
        $response = $this->api->createGeoZone($data);
        
        if (! $response->ok()) return $this->apiError($response);
        return redirect()->route('admin.geo-zones.index')->with('success', 'Zona creada');
    }

    public function edit(string $id): View
    {
        $response = $this->api->getGeoZone($id);
        if (! $response->ok()) return $this->apiError($response);
        
        return view('admin.geo-zones.edit', [
            'zone' => $response->json('zone'),
        ]);
    }

    public function update(UpdateGeoZoneRequest $request, string $id): RedirectResponse
    {
        $response = $this->api->updateGeoZone($id, $request->validated());
        if (! $response->ok()) return $this->apiError($response);
        return redirect()->route('admin.geo-zones.index')->with('success', 'Zona actualizada');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->api->deleteGeoZone($id);
        if (! $response->ok()) return $this->apiError($response);
        return redirect()->route('admin.geo-zones.index')->with('success', 'Zona eliminada');
    }

    // Polígonos
    public function addPolygon(string $zoneId, StorePolygonRequest $request): RedirectResponse
    {
        $response = $this->api->addPolygon($zoneId, $request->validated());
        return $response->ok() 
            ? back()->with('success', 'Polígono añadido')
            : $this->apiError($response);
    }
    // ... updatePolygon, deletePolygon
}
```

---

## 5. Frontend - Componentes Clave

### 5.1 Mapa Geo-Zonas (Leaflet + Alpine.js)

```blade
{{-- resources/views/admin/geo-zones/partials/map.blade.php --}}
<div x-data="geoZoneMap()" x-init="init()" class="h-[600px] w-full rounded-lg border">
    <div id="map" class="h-full w-full"></div>
    
    <template x-if="polygons.length > 0">
        <div class="absolute bottom-4 right-4 bg-white p-3 rounded shadow z-10">
            <h4 class="font-bold">Polígonos ({{ polygons.length }})</h4>
            <ul class="text-sm">
                <template x-for="(poly, i) in polygons" :key="poly.id">
                    <li class="flex items-center gap-2">
                        <span x-text="poly.name"></span>
                        <button @click="removePolygon(i)" class="text-red-500">×</button>
                    </li>
                </template>
            </ul>
            <input type="hidden" name="polygons" :value="JSON.stringify(polygons)">
        </div>
    </template>
</div>

<script>
function geoZoneMap() {
    return {
        map: null,
        drawnItems: null,
        drawControl: null,
        polygons: [],
        
        init() {
            this.map = L.map(this.$refs.map || this.$el.querySelector('#map'))
                .setView([-34.6037, -58.3816], 10);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(this.map);
            
            this.drawnItems = new L.FeatureGroup();
            this.map.addLayer(this.drawnItems);
            
            this.drawControl = new L.Control.Draw({
                edit: { featureGroup: this.drawnItems },
                draw: {
                    polygon: { allowIntersection: false, showArea: true },
                    polyline: false, circle: false, rectangle: false, marker: false
                }
            });
            this.map.addControl(this.drawControl);
            
            this.map.on(L.Draw.Event.CREATED, (e) => this.onCreate(e));
            this.map.on(L.Draw.Event.EDITED, (e) => this.onEdit(e));
            this.map.on(L.Draw.Event.DELETED, (e) => this.onDelete(e));
            
            // Cargar polígonos existentes si editando
            if (window.existingPolygons) {
                this.loadPolygons(window.existingPolygons);
            }
        },
        
        onCreate(e) {
            const layer = e.layer;
            this.drawnItems.addLayer(layer);
            const geojson = layer.toGeoJSON();
            this.polygons.push({
                id: crypto.randomUUID(),
                name: `Polígono ${this.polygons.length + 1}`,
                geometry: geojson.geometry,
                sort_order: this.polygons.length
            });
            this.updateHiddenInput();
        },
        
        onEdit(e) {
            const layers = e.layers;
            layers.eachLayer((layer) => {
                const idx = this.polygons.findIndex(p => p._leaflet_id === layer._leaflet_id);
                if (idx !== -1) {
                    this.polygons[idx].geometry = layer.toGeoJSON().geometry;
                }
            });
            this.updateHiddenInput();
        },
        
        onDelete(e) {
            const layers = e.layers;
            layers.eachLayer((layer) => {
                this.polygons = this.polygons.filter(p => p._leaflet_id !== layer._leaflet_id);
            });
            this.reindexPolygons();
            this.updateHiddenInput();
        },
        
        removePolygon(index) {
            const poly = this.polygons[index];
            this.drawnItems.eachLayer((layer) => {
                if (layer._leaflet_id === poly._leaflet_id) {
                    this.drawnItems.removeLayer(layer);
                }
            });
            this.polygons.splice(index, 1);
            this.reindexPolygons();
            this.updateHiddenInput();
        },
        
        loadPolygons(polygons) {
            polygons.forEach((p, i) => {
                const layer = L.geoJSON(p.geometry).addTo(this.drawnItems);
                this.polygons.push({
                    ...p,
                    _leaflet_id: layer._leaflet_id
                });
            });
            if (this.polygons.length > 0) {
                this.map.fitBounds(this.drawnItems.getBounds());
            }
        },
        
        reindexPolygons() {
            this.polygons.forEach((p, i) => p.sort_order = i);
        },
        
        updateHiddenInput() {
            this.$el.querySelector('input[name="polygons"]').value = JSON.stringify(this.polygons);
        }
    }
}
</script>
```

### 5.2 Tabla Ordenable Profile Fields (Alpine.js + SortableJS)

```blade
{{-- resources/views/admin/profile-fields/index.blade.php --}}
<table x-data="sortableTable()" class="w-full">
    <thead>
        <tr>
            <th class="w-10">Orden</th>
            <th>Slug</th>
            <th>Etiqueta</th>
            <th>Tipo</th>
            <th>Visibilidad</th>
            <th>Req. Verif.</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody x-ref="tbody" class="sortable">
        @foreach($fields as $field)
        <tr data-id="{{ $field['id'] }}">
            <td class="drag-handle">⋮⋮</td>
            <td>{{ $field['slug'] }}</td>
            <td>{{ $field['label'] }}</td>
            <td><span class="badge">{{ $field['type'] }}</span></td>
            <td>{{ $field['default_visibility'] }}</td>
            <td>{{ $field['default_requires_verified'] ? 'Sí' : 'No' }}</td>
            <td>
                <button @click="toggleActive('{{ $field['id'] }}', {{ !$field['is_active'] }})"
                        class="toggle {{ $field['is_active'] ? 'on' : 'off' }}">
                </button>
            </td>
            <td>
                <a href="{{ route('admin.profile-fields.edit', $field['id']) }}">Editar</a>
                <button @click="deleteField('{{ $field['id'] }}')" class="text-red-500">Eliminar</button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
function sortableTable() {
    return {
        init() {
            Sortable.create(this.$refs.tbody, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: (evt) => this.saveOrder(evt)
            });
        },
        
        saveOrder(evt) {
            const ids = Array.from(this.$refs.tbody.querySelectorAll('tr'))
                .map(tr => tr.dataset.id);
            fetch('{{ route("admin.profile-fields.reorder") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ids })
            });
        },
        
        async toggleActive(id, active) {
            await fetch(`{{ route('admin.profile-fields.update', ':id') }}`.replace(':id', id), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ is_active: active })
            });
        },
        
        async deleteField(id) {
            if (!confirm('¿Eliminar este campo?')) return;
            await fetch(`{{ route('admin.profile-fields.destroy', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            location.reload();
        }
    }
}
</script>
```

---

## 6. Rutas Web Admin (routes/web.php)

```php
<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoZoneController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileFieldController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Middleware\AdminWebGuardMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Auth Web
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
        Route::get('/forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('/forgot-password', [AdminAuthController::class, 'sendResetLink'])->name('forgot-password.post');
        Route::get('/reset-password/{token}', [AdminAuthController::class, 'showResetPassword'])->name('reset-password');
        Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('reset-password.post');
    });

    Route::middleware([AdminWebGuardMiddleware::class])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Geo Zonas
        Route::resource('geo-zones', GeoZoneController::class)
            ->parameters(['geo-zones' => 'zone'])
            ->except(['show']);
        Route::post('geo-zones/{zone}/polygons', [GeoZoneController::class, 'addPolygon'])->name('geo-zones.polygons.store');
        Route::put('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'updatePolygon'])->name('geo-zones.polygons.update');
        Route::delete('geo-zones/{zone}/polygons/{polygon}', [GeoZoneController::class, 'deletePolygon'])->name('geo-zones.polygons.destroy');
        
        // Usuarios
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/change-role', [UserController::class, 'changeRole'])->name('users.change-role');
        Route::get('users/{user}/export', [UserController::class, 'export'])->name('users.export');
        
        // Profile Fields
        Route::resource('profile-fields', ProfileFieldController::class)->except(['show']);
        Route::post('profile-fields/reorder', [ProfileFieldController::class, 'reorder'])->name('profile-fields.reorder');
        
        // Verificaciones
        Route::get('verifications', [VerificationController::class, 'index'])->name('verifications.index');
        Route::get('verifications/{verification}', [VerificationController::class, 'show'])->name('verifications.show');
        Route::post('verifications/{verification}/approve', [VerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('verifications/{verification}/reject', [VerificationController::class, 'reject'])->name('verifications.reject');
        
        // Configuración
        Route::get('config', [ConfigController::class, 'index'])->name('config.index');
        Route::put('config', [ConfigController::class, 'update'])->name('config.update');
        
        // API Keys
        Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::get('api-keys/create', [ApiKeyController::class, 'create'])->name('api-keys.create');
        Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::get('api-keys/{apiKey}/created', [ApiKeyController::class, 'showCreated'])->name('api-keys.show-created');
        Route::delete('api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
        
        // Auditoría
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
```

---

## 7. Configuración y Despliegue

### 7.1 Variables de Entorno (.env)
```env
APP_NAME="Altobul Admin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.altobul.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=altobul_admin
DB_USERNAME=altobul_admin
DB_PASSWORD=secure_password

# Backend API
ADMIN_API_BASE_URL=https://api.altobul.com/api
ADMIN_API_KEY=ak_admin_xxxxxxxxxxxxxxxxx

# Sanctum
SESSION_DOMAIN=.altobul.com
SANCTUM_STATEFUL_DOMAINS=admin.altobul.com
SESSION_SECURE_COOKIE=true

# Cache/Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=redis_password
REDIS_PORT=6379

# Files
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=altobul-admin
AWS_URL=https://cdn.altobul.com
```

### 7.2 Supervisor (Queue Workers)
```ini
; /etc/supervisor/conf.d/altobul-admin-worker.conf
[program:altobul-admin-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/altobul-admin/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/altobul-admin/storage/logs/queue-worker.log
```

### 7.3 Nginx Config
```nginx
server {
    listen 443 ssl http2;
    server_name admin.altobul.com;
    root /var/www/altobul-admin/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/admin.altobul.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/admin.altobul.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Assets cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 7.4 Deploy Script (GitHub Actions / CI)
```yaml
# .github/workflows/deploy.yml
name: Deploy Admin

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pgsql, redis, gd, zip, bcmath
          
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
        
      - name: Build assets
        run: npm ci && npm run build
        
      - name: Run tests
        run: php artisan test
        
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: www-data
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/altobul-admin
            git pull origin main
            composer install --no-dev --optimize-autoloader
            npm ci && npm run build
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            sudo supervisorctl restart altobul-admin-queue:*
```

---

## 8. Checklist de Implementación

### Backend (API Admin - Pendientes)
- [ ] `DashboardController` con endpoints `/dashboard/metrics` y `/dashboard/charts`
- [ ] Tests para nuevos endpoints dashboard
- [ ] Rate limiting específico para admin API

### Frontend (Admin App)
- [ ] Setup Laravel 12 project
- [ ] Configurar Tailwind + Alpine.js + Leaflet
- [ ] Implementar `BackendApiService`
- [ ] Middleware `InjectAdminApiToken`
- [ ] Auth controllers (login, password reset)
- [ ] Layout principal (sidebar, topbar, responsive)
- [ ] **Dashboard**: KPI cards + Gráficos (Chart.js)
- [ ] **Geo-Zonas**: CRUD completo + Mapa Leaflet.draw
- [ ] **Usuarios**: Listado filtros + Detalle tabs + Acciones bulk
- [ ] **Profile Fields**: CRUD + Drag&Drop reorder + Gestor opciones
- [ ] **Verificaciones**: Cola + Detalle + Aprobar/Rechazar
- [ ] **Configuración**: Tabla key/value + JSON editor
- [ ] **API Keys**: Listar + Crear (mostrar raw once) + Revocar
- [ ] **Auditoría**: Tabla filtrable + Export CSV
- [ ] Tests feature (Pest/PHPUnit)
- [ ] CI/CD pipeline

### Seguridad
- [ ] HTTPS obligatorio (HSTS)
- [ ] CSP headers
- [ ] Rate limiting en login admin
- [ ] 2FA para admins (opcional: Laravel Fortify)
- [ ] Logs de auditoría inmutables
- [ ] Rotación API Keys periódica

---

## 9. Referencias Backend (Resumen Rápido)

| Recurso | Archivo/Ruta |
|---------|--------------|
| Rutas API Admin | `routes/api.php` líneas 174-244 |
| Middleware API Key | `app/Http/Middleware/ApiKeyMiddleware.php` |
| Middleware Admin Auth | `app/Http/Middleware/AdminAuthorizationMiddleware.php` |
| Middleware Web Admin | `app/Http/Middleware/AdminWebGuardMiddleware.php` |
| Controladores Admin | `app/Http/Controllers/Admin/*.php` |
| Modelos Geo | `app/Models/GeoZone.php`, `GeoPolygon.php` |
| Modelos User/Profile | `app/Models/User.php`, `Profile.php` |
| Profile Fields | `app/Models/ProfileFieldDefinition.php`, `ProfileFieldOption.php` |
| Verificación | `app/Models/VerificationRequest.php` |
| Config | `app/Models/AppConfig.php` |
| API Keys | `app/Models/ApiKey.php`, `app/Services/ApiKeyService.php` |
| Auditoría | `app/Models/AdminAuditLog.php` |
| Autorización | `app/Services/Authorization/AuthorizationService.php` |
| Geo Service | `app/Services/Geo/GeoZoneService.php` |
| Recursos API | `app/Http/Resources/*.php` |

---

*Documento generado automáticamente basado en análisis del backend Altobul (Laravel 12)*
*Fecha: 2026-07-28*
*Versión: 1.0*