# Documentación de la API Altobul

## Visión General

Altobul es una plataforma de interacción social progresiva que sigue el flujo **Descubrir → Toke → Match (7 días) → Amistad (permanente)**. La API proporciona un backend completo para construir aplicaciones cliente (web, móvil) y paneles administrativos.

## Arquitectura

### Arquitectura Multi-App

El backend sirve múltiples aplicaciones:

| Aplicación | Prefijo API | Autenticación |
|------------|-------------|---------------|
| **Cliente** (usuario final) | `/api/client/*` | API Key (CLIENT) + Sanctum Token |
| **Admin** (administradores) | `/api/admin/*` | API Key (ADMIN) + Sanctum Token + Rol Admin |
| **Instalador** | `/api/install/*` | Ninguna (configuración única) |

### Sistema Dual de Autenticación

1. **API Key** - Identifica la *aplicación* (Cliente/Admin/Móvil/Integración)
   - Header: `X-API-Key: <key>`
   - Tipos: `CLIENT`, `ADMIN`, `MOBILE`, `INTEGRATION`

2. **Sanctum Token** - Identifica al *usuario* (persona)
   - Header: `Authorization: Bearer <token>`
   - Se obtiene via `/auth/login`

### Conceptos Clave del Dominio

| Concepto | Descripción | TTL |
|----------|-------------|-----|
| **Descubrir** | Descubrir usuarios cercanos | N/A |
| **Toke** | Señal de interés enviada a otro usuario | 48 horas |
| **Match** | Toke mutuo → conexión de 7 días | 7 días |
| **Amistad** | Amistad permanente desde match | Permanente |
| **Solicitud Amistad** | Solicitud directa de amistad (salta match) | 7 días |

### Niveles de Privacidad

| Nivel | Visibilidad |
|-------|-------------|
| `PUBLIC` | Todos |
| `MATCH` | Solo matches actuales |
| `FRIENDS` | Solo amigos |
| `PRIVATE` | Solo con grant explícito |
| `+ verified_only` | Opcional: solo usuarios verificados |

### Estado de Verificación

| Estado | Descripción |
|--------|-------------|
| `not_verified` | Por defecto, sin verificación |
| `pending` | En revisión por admin |
| `verified` | Aprobado por admin |

### Geo-Zonas

Polígonos geográficos definidos por admin (PostGIS) para descubrimiento y filtrado basado en ubicación.

### TTL de Contenido

| Contenido | TTL |
|-----------|-----|
| Toke | 48 horas |
| Match | 7 días |
| Post | 24 horas (configurable) |
| Amistad | Permanente |
| Solicitud Amistad | 7 días |

## URL Base

```
https://api.example.com/api
```

Todos los endpoints llevan prefijo `/api`.

## Versionado

Versión actual: **v1**
- Header opcional: `Accept: application/vnd.altobul.v1+json`
- Prefijo URL futuro: `/api/v1/`

## Rate Limiting

| Categoría | Límite |
|-----------|--------|
| Registro | 5/min |
| Login | 10/min |
| Recuperar contraseña | 3/min |
| Instalación | 10/hora |
| Acciones sensibles (tokes, posts, fotos) | 30/min |
| Mensajes | 60/min |
| API General | 60/min |

Headers de respuesta:
- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `Retry-After` (cuando limitado)

## Formato de Respuesta

### Éxito

```json
{
  "data": { ... },
  "meta": { ... }
}
```

### Error

```json
{
  "error": "Tipo de Error",
  "message": "Descripción legible",
  "errors": { "campo": ["mensaje de error"] }
}
```

### Paginación

```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "from": 1,
    "to": 20
  }
}
```

## Códigos HTTP Comunes

| Código | Significado |
|--------|-------------|
| 200 | OK |
| 201 | Creado |
| 400 | Bad Request |
| 401 | Unauthorized (token inválido/faltante) |
| 403 | Forbidden (autorización falló) |
| 404 | Not Found |
| 422 | Error de Validación |
| 429 | Too Many Requests |
| 500 | Error Servidor |

## Flujo de Instalación

1. `GET /api/install/status` - Verificar si instalado
2. `POST /api/install` - Instalar backend (config DB + usuario admin)
3. `POST /api/admin/api-keys` - Crear API Key CLIENT (solo admin)
4. Configurar App Cliente con URL backend + Client API Key
5. Usuarios registran via `POST /api/client/auth/register`

## Eventos Tiempo Real (WebSockets)

Usando **Laravel Reverb** con canales privados:

| Evento | Canal | Payload |
|--------|-------|---------|
| `toke.received` | `user.{receiverId}` | NewTokeReceived |
| `match.created` | `user.{userA}`, `user.{userB}` | NewMatch |
| `friendship.created` | `user.{userA}`, `user.{userB}` | NewFriendship |
| `message.created` | `conversation.{id}` | NewMessage |
| `grant.created` | `user.{granteeId}` | NewGrant |

Autorización canales: `POST /broadcasting/auth` (requiere token Sanctum)

## Versión

**v1.1** - Agregados: Profile Field Grants, Eventos Tiempo Real, Photo/Post Grants