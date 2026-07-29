# Endpoints Detallados

## Convenciones

| Convención | Descripción |
|------------|-------------|
| `GET/POST/PUT/DELETE` | Método HTTP |
| `:param` | Parámetro de ruta (UUID) |
| `?query` | Query string opcional |
| `Headers` | `X-API-Key` + `Authorization: Bearer` |
| `200/201/4xx/5xx` | Códigos de estado esperados |

---

## Sistema e Instalación

### Health Check
```
GET /api/system/health
```
**Público** - Sin autenticación

**Respuesta 200:**
```json
{
  "status": "ok",
  "installed": true,
  "version": "1.0.0",
  "api_version": "v1",
  "compatible_clients": ["1.0.0"],
  "database": "connected",
  "timestamp": "2026-07-28T10:30:00.000000Z"
}
```

### Compatibilidad
```
GET /api/system/compatibility
```
**Público**

**Respuesta 200:**
```json
{
  "installed": true,
  "api_version": "v1",
  "minimum_client_version": "1.0.0",
  "compatible_applications": {
    "client": ["1.0.0"],
    "admin": ["1.0.0"],
    "mobile": ["1.0.0"]
  },
  "requirements": {
    "php": ">=8.2",
    "database": "PostgreSQL 15+ with PostGIS"
  }
}
```

### Estado Instalación
```
GET /api/install/status
```
**Público**

**Respuesta 200:**
```json
{
  "installed": false,
  "checks": {
    "database": true,
    "migrations": true,
    "app_config": false,
    "first_admin": false
  },
  "ready": false
}
```

### Instalar Backend
```
POST /api/install
Rate limit: 10/hora
```

**Body:**
```json
{
  "email": "admin@example.com",
  "password": "securepassword",
  "password_confirmation": "securepassword",
  "app_name": "Mi App"
}
```

**Respuesta 201:**
```json
{
  "message": "Backend installed successfully",
  "admin": {
    "id": "uuid",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

---

## Autenticación Cliente (`/api/client/auth`)

### Registrar Usuario
```
POST /api/client/auth/register
X-API-Key: <client-key>
Rate limit: 5/min
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "Mi App"
}
```

**Respuesta 201:**
```json
{
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "role": "user",
    "verification_status": "not_verified",
    "status": "active",
    "created_at": "2026-07-28T10:00:00.000000Z"
  },
  "message": "Registration successful. Please verify your email."
}
```

### Login
```
POST /api/client/auth/login
X-API-Key: <client-key>
Rate limit: 10/min
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "Mi App"
}
```

**Respuesta 200:**
```json
{
  "user": { "id": "uuid", "email": "...", "role": "user", "verification_status": "verified" },
  "token": "1|abc123...",
  "expires_at": "2026-08-04T10:30:00.000000Z"
}
```

### Logout
```
POST /api/client/auth/logout
Authorization: Bearer <token>
X-API-Key: <client-key>
```

**Respuesta 200:**
```json
{ "message": "Logged out successfully" }
```

### Refresh Token
```
POST /api/client/auth/refresh
Authorization: Bearer <token>
X-API-Key: <client-key>
```

**Respuesta 200:** Igual que login

### Usuario Actual
```
GET /api/client/auth/me
Authorization: Bearer <token>
X-API-Key: <client-key>
```

**Respuesta 200:**
```json
{
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "role": "user",
    "verification_status": "verified",
    "status": "active",
    "email_verified_at": "2026-07-28T10:00:00.000000Z",
    "profile": { ... },
    "created_at": "2026-07-28T10:00:00.000000Z"
  }
}
```

### Reenviar Verificación Email
```
POST /api/client/auth/resend-verification
Authorization: Bearer <token>
X-API-Key: <client-key>
```

### Verificar Email (Link)
```
GET /api/client/auth/verify-email/{id}/{hash}
X-API-Key: <client-key>
```
*Sin token de usuario - usa link firmado del email*

### Solicitar Verificación Identidad
```
POST /api/client/auth/verification/request
Authorization: Bearer <token>
X-API-Key: <client-key>
```

**Body:**
```json
{
  "verification_method": "document", // document, video, manual
  "external_reference": "REF123456"
}
```

### Estado Verificación Identidad
```
GET /api/client/auth/verification/status
Authorization: Bearer <token>
X-API-Key: <client-key>
```

### Olvidé Contraseña
```
POST /api/client/auth/forgot-password
X-API-Key: <client-key>
Rate limit: 3/min
```

**Body:** `{ "email": "user@example.com" }`

### Resetear Contraseña
```
POST /api/client/auth/reset-password
X-API-Key: <client-key>
Rate limit: 3/min
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "newpassword",
  "password_confirmation": "newpassword",
  "token": "token-del-email"
}
```

---

## Perfil (`/api/client/profile`)

*Todos requieren: `Authorization: Bearer <token>`, `X-API-Key: <client-key>`, `verified`*

### Ver Mi Perfil
```
GET /api/client/profile
```

**Respuesta 200:**
```json
{
  "profile": {
    "user_id": "uuid",
    "title": "Desarrollador",
    "description": "Me gusta programar",
    "birth_date": "1990-01-15",
    "gender": "male",
    "looking_for": "female",
    "min_age": 25,
    "max_age": 35,
    "max_distance_km": 50,
    "location": { "type": "Point", "coordinates": [-58.38, -34.60] },
    "location_precision_meters": 1000,
    "discoverable": true,
    "profile_visibility": "PUBLIC",
    "verification_status": "verified",
    "field_values": [...],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

### Actualizar Perfil
```
PUT /api/client/profile
```

**Body (campos opcionales):**
```json
{
  "title": "Nuevo título",
  "description": "Nueva descripción",
  "birth_date": "1990-01-15",
  "gender": "male",
  "looking_for": "female",
  "min_age": 25,
  "max_age": 35,
  "max_distance_km": 50,
  "discoverable": true,
  "profile_visibility": "PUBLIC"
}
```

### Actualizar Ubicación
```
PUT /api/client/profile/location
```

**Body:**
```json
{
  "latitude": -34.6037,
  "longitude": -58.3816,
  "precision_meters": 1000
}
```

### Listar Definiciones de Campos
```
GET /api/client/profile/fields
```

**Respuesta 200:**
```json
{
  "fields": [
    {
      "id": "uuid",
      "slug": "intereses",
      "label": "Intereses",
      "type": "multi_select",
      "options": [
        { "id": "uuid", "value": "programacion", "label": "Programación" },
        { "id": "uuid", "value": "musica", "label": "Música" }
      ],
      "visibility": "PUBLIC",
      "is_required": false,
      "sort_order": 0
    }
  ]
}
```

### Ver Campo Específico
```
GET /api/client/profile/fields/{slug}
```

### Establecer Valor Campo
```
PUT /api/client/profile/fields/{slug}
```

**Body:**
```json
{
  "value": "texto libre",
  "selected_options": ["opt1", "opt2"]
}
```

### Eliminar Campo
```
DELETE /api/client/profile/fields/{slug}
```

---

## Grants de Campos Privados (`/api/client/profile/fields/{fieldValue}/grants`)

*Requiere: campo con visibility=PRIVATE, usuario dueño del campo*

### Listar Grants
```
GET /api/client/profile/fields/{fieldValue}/grants
```

### Crear Grant
```
POST /api/client/profile/fields/{fieldValue}/grants
```

**Body:**
```json
{
  "grantee_id": "uuid-usuario",
  "expires_at": "2026-12-31T23:59:59.000000Z" // opcional
}
```

### Revocar Grant
```
DELETE /api/client/profile/fields/{fieldValue}/grants/{grantee}
```

---

## Usuarios Públicos (`/api/client/users`)

### Ver Perfil Público
```
GET /api/client/users/{user:id}
Authorization: Bearer <token>
X-API-Key: <client-key>
```

**Respuesta 200:**
```json
{
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "role": "user",
    "verification_status": "verified",
    "status": "active",
    "created_at": "..."
  },
  "profile": {
    "user_id": "uuid",
    "title": "...",
    "description": "...",
    "birth_date": "1990-01-15",
    "gender": "male",
    "location": { "type": "Point", "coordinates": [...] },
    "profile_visibility": "PUBLIC",
    "field_values": [...],
    "photos": [...],
    "posts": [...]
  }
}
```

*Respeta privacy levels, grants, blocks, verificación*

### Fotos del Usuario
```
GET /api/client/users/{user}/photos
```

### Posts del Usuario
```
GET /api/client/users/{user}/posts
```

---

## Tokes (`/api/client/tokes`)

*Requiere: `verified`, `throttle:api-sensitive` (30/min)*

### Enviar Toke
```
POST /api/client/tokes
```

**Body:**
```json
{ "receiver_id": "uuid" }
```

**Respuesta 201:**
```json
{
  "toke": {
    "id": "uuid",
    "sender_id": "uuid",
    "receiver_id": "uuid",
    "status": "ACTIVE",
    "expires_at": "2026-07-30T10:00:00.000000Z",
    "created_at": "...",
    "sender": { "id": "...", "profile": {...} },
    "receiver": { "id": "...", "profile": {...} }
  }
}
```
*Dispara evento WebSocket `toke.received` al receptor*

### Listar Tokes
```
GET /api/client/tokes
```

**Respuesta 200:**
```json
{
  "sent": { "data": [...], "pagination": {...} },
  "received": { "data": [...], "pagination": {...} }
}
```

### Consumir Toke (Crear Match)
```
POST /api/client/tokes/{toke}/consume
```
*Solo el receptor puede consumir*

**Respuesta 200:**
```json
{
  "toke": { "id": "...", "status": "CONSUMED", "matched_at": "..." },
  "match_created": true,
  "mutual_toke": false
}
```
*Si match_created=true, dispara evento `match.created` a ambos usuarios*

### Cancelar Toke Propio
```
DELETE /api/client/tokes/{toke}
```
*Solo el emisor puede cancelar*

---

## Matches (`/api/client/matches`)

*Requiere: `verified`*

### Listar Matches Activos
```
GET /api/client/matches
```

**Respuesta 200:**
```json
{
  "matches": [
    {
      "id": "uuid",
      "user_a_id": "uuid",
      "user_b_id": "uuid",
      "status": "ACTIVE",
      "expires_at": "2026-08-04T10:00:00.000000Z",
      "created_at": "...",
      "user_a": { "id": "...", "profile": {...} },
      "user_b": { "id": "...", "profile": {...} }
    }
  ],
  "pagination": {...}
}
```
*Expiran a los 7 días automáticamente*

### Convertir Match en Amistad
```
POST /api/client/matches/{match}/convert-to-friendship
```

**Respuesta 200:**
```json
{
  "friendship": { "id": "...", "status": "ACTIVE", "created_at": "..." },
  "match": { "id": "...", "status": "ENDED", "ended_at": "..." }
}
```
*Dispara evento `friendship.created`*

---

## Amistades (`/api/client/friendships`)

*Requiere: `verified`*

### Listar Amistades
```
GET /api/client/friendships
```

### Solicitar Amistad Directa
```
POST /api/client/friendships
```

**Body:**
```json
{ "addressee_id": "uuid" }
```
*Crea FriendshipRequest (expira 7 días), no requiere match previo*

### Terminar Amistad
```
DELETE /api/client/friendships/{friendship}
```
*Cualquiera de los dos puede terminar*

---

## Solicitudes de Amistad (`/api/client/friendship-requests`)

*Requiere: `verified`*

### Listar (Enviadas y Recibidas)
```
GET /api/client/friendship-requests
```

**Respuesta 200:**
```json
{
  "sent": { "data": [...], "pagination": {...} },
  "received": { "data": [...], "pagination": {...} }
}
```

### Aceptar Solicitud
```
POST /api/client/friendship-requests/{friendshipRequest}/accept
```
*Solo el destinatario*

### Rechazar Solicitud
```
POST /api/client/friendship-requests/{friendshipRequest}/reject
```
*Solo el destinatario*

### Cancelar/Rechazar (Eliminar)
```
DELETE /api/client/friendship-requests/{friendshipRequest}
```
*Emisor puede cancelar, destinatario puede rechazar*

---

## Bloqueos (`/api/client/blocks`)

*Requiere: `verified`*

### Listar Bloqueos
```
GET /api/client/blocks
```

### Bloquear Usuario
```
POST /api/client/blocks
```

**Body:**
```json
{ "blocked_id": "uuid" }
```
*Absoluto: anula matches, amistades, conversaciones, visibilidad*

### Desbloquear
```
DELETE /api/client/blocks/{block}
```
*Solo el bloqueador*

---

## Conversaciones (`/api/client/conversations`)

*Requiere: `verified`, `throttle:api-sensitive` en create*

### Listar Conversaciones
```
GET /api/client/conversations
```

### Crear/Obtener Conversación
```
POST /api/client/conversations
```

**Body:**
```json
{ "recipient_id": "uuid" }
```
*Reutiliza conversación existente si hay match/amistad*

### Ver Conversación
```
GET /api/client/conversations/{conversation}
```

### Terminar Conversación
```
DELETE /api/client/conversations/{conversation}
```

---

## Mensajes (`/api/client/conversations/{conversation}/messages`)

*Requiere: `verified`, `throttle:api-messages` (60/min)*

### Listar Mensajes
```
GET /api/client/conversations/{conversation}/messages
```

### Enviar Mensaje
```
POST /api/client/conversations/{conversation}/messages
```

**Body:**
```json
{ "content": "Hola! ¿Cómo estás?" }
```
*Máx 5000 chars. Dispara evento `message.created`*

---

## Fotos (`/api/client/photos`)

*Requiere: `verified`, `throttle:api-sensitive` en upload*

### Listar Mis Fotos
```
GET /api/client/photos
```

### Subir Foto
```
POST /api/client/photos
Content-Type: multipart/form-data
```

**Body (form-data):**
```
photo: <file> (max 10MB, jpeg/png/webp)
visibility: PUBLIC|MATCH|FRIENDS|PRIVATE
requires_verified: true|false
```

**Respuesta 202:**
```json
{
  "photo": {
    "id": "uuid",
    "user_id": "uuid",
    "storage_key": "users/.../photo.jpg",
    "visibility": "PRIVATE",
    "requires_verified": true,
    "status": "PROCESSING",
    "sort_order": 0,
    "created_at": "..."
  }
}
```
*Procesamiento asíncrono*

### Ver Foto
```
GET /api/client/photos/{photo}
```
*Respeta privacy, grants, verificación, bloques*

### Eliminar Foto Propia
```
DELETE /api/client/photos/{photo}
```

---

## Grants de Fotos (`/api/client/photos/{photo}/grants`)

*Solo para fotos PRIVATE, dueño de la foto*

### Listar Grants
```
GET /api/client/photos/{photo}/grants
```

### Otorgar Acceso
```
POST /api/client/photos/{photo}/grants
```

**Body:**
```json
{ "grantee_id": "uuid" }
```

### Revocar Acceso
```
DELETE /api/client/photos/{photo}/grants/{grantee}
```

---

## Posts (`/api/client/posts`)

*Requiere: `verified`, `throttle:api-sensitive` en create*

### Listar Mis Posts
```
GET /api/client/posts
```
*Solo ACTIVE y no expirados*

### Crear Post
```
POST /api/client/posts
```

**Body:**
```json
{
  "content": "Contenido en markdown",
  "visibility": "PUBLIC|MATCH|FRIENDS|PRIVATE",
  "requires_verified": false,
  "expires_at": "2026-07-29T20:00:00.000000Z" // opcional, máx 24h
}
```

### Ver Post
```
GET /api/client/posts/{post}
```
*Respeta privacy, grants, expiración, verificación, bloques*

### Eliminar Post Propio
```
DELETE /api/client/posts/{post}
```

---

## Grants de Posts (`/api/client/posts/{post}/grants`)

*Solo posts PRIVATE, dueño del post*

### Listar / Crear / Eliminar
Igual que fotos grants.

---

## Descubrimiento (`/api/client/discover`)

*Requiere: `verified`*

### Descubrir General
```
GET /api/client/discover
```

### Usuarios Online
```
GET /api/client/discover/online
```

### Usuarios Recientes
```
GET /api/client/discover/recent
```

### Cercanos (requiere ubicación)
```
GET /api/client/discover/nearby
```

**Query params (todos):**
```
?limit=20&verified_only=true&order=distance&fields=profile,photos
```

**Respuesta 200:**
```json
{
  "users": [
    {
      "id": "uuid",
      "email": "...",
      "verification_status": "verified",
      "profile": {...},
      "distance_km": 2.5,
      "is_online": true,
      "last_seen_at": "..."
    }
  ],
  "pagination": {...}
}
```

---

## Administración (`/api/admin`)

*Requiere: `X-API-Key: <admin-key>`, `Authorization: Bearer <admin-token>`, `role: admin`*

### Auth Admin
Igual que cliente pero con API Key ADMIN.

### Configuración
```
GET /api/admin/config
PUT /api/admin/config
```

### API Keys
```
GET    /api/admin/api-keys
POST   /api/admin/api-keys
GET    /api/admin/api-keys/{apiKey}
DELETE /api/admin/api-keys/{apiKey}
```

**POST Body:**
```json
{
  "name": "Mi App Cliente",
  "type": "CLIENT", // CLIENT, ADMIN, MOBILE, INTEGRATION
  "expires_in_days": 365
}
```

**Respuesta 201:**
```json
{
  "api_key": { "id": "...", "name": "...", "type": "CLIENT", ... },
  "raw_key": "altobul_cli_abc123...",
  "warning": "This is the only time the raw key will be shown. Store it securely."
}
```

### Definiciones de Campos Perfil
```
GET    /api/admin/profile-fields
POST   /api/admin/profile-fields
GET    /api/admin/profile-fields/{field}
PUT    /api/admin/profile-fields/{field}
DELETE /api/admin/profile-fields/{field}
POST   /api/admin/profile-fields/reorder
```

### Verificación de Usuarios
```
GET    /api/admin/verification-requests?status=PENDING
GET    /api/admin/verification-requests/{request}
POST   /api/admin/verification-requests/{request}/approve
POST   /api/admin/verification-requests/{request}/reject
```

**Reject Body:**
```json
{ "rejection_reason": "Documento ilegible" }
```

### Geo-Zonas
```
GET    /api/admin/geo-zones
POST   /api/admin/geo-zones
GET    /api/admin/geo-zones/{zone}
PUT    /api/admin/geo-zones/{zone}
DELETE /api/admin/geo-zones/{zone}

POST   /api/admin/geo-zones/{zone}/polygons
PUT    /api/admin/geo-zones/{zone}/polygons/{polygon}
DELETE /api/admin/geo-zones/{zone}/polygons/{polygon}
```

### Gestión Usuarios
```
GET    /api/admin/users?search=&role=&status=&verification_status=
GET    /api/admin/users/{user}
POST   /api/admin/users/{user}/suspend
POST   /api/admin/users/{user}/activate
POST   /api/admin/users/{user}/change-role
```

**Change Role Body:**
```json
{ "role": "admin" } // user|admin
```

### Audit Logs
```
GET /api/admin/audit-logs
```

---

## WebSockets (Broadcasting)

### Autenticación Canales
```
POST /api/broadcasting/auth
Authorization: Bearer <token>
X-API-Key: <client-key>
```

### Canales Privados

| Evento | Canal | Payload |
|--------|-------|---------|
| `toke.received` | `user.{receiverId}` | NewTokeReceived |
| `match.created` | `user.{userA}`, `user.{userB}` | NewMatch |
| `friendship.created` | `user.{userA}`, `user.{userB}` | NewFriendship |
| `message.created` | `conversation.{id}` | NewMessage |
| `grant.created` | `user.{granteeId}` | NewGrant |

### Estructura Eventos

**NewTokeReceived:**
```json
{
  "toke": { "id": "...", "sender": {...}, "receiver": {...}, "expires_at": "...", "created_at": "..." }
}
```

**NewMatch:**
```json
{
  "match": { "id": "...", "user_a": {...}, "user_b": {...}, "expires_at": "...", "created_at": "..." }
}
```

**NewFriendship:**
```json
{
  "friendship": { "id": "...", "user_a": {...}, "user_b": {...}, "created_at": "..." }
}
```

**NewMessage:**
```json
{
  "message": { "id": "...", "conversation_id": "...", "sender": {...}, "content": "...", "created_at": "..." }
}
```

**NewGrant:**
```json
{
  "grant": { "id": "...", "field_value": {...}, "grantee": {...}, "granted_by": {...}, "expires_at": "..." }
}
```