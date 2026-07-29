# Manejo de Errores y Códigos de Estado

## Códigos HTTP

| Código | Significado | Acción |
|--------|-------------|--------|
| **200** | OK | Éxito (GET, PUT, DELETE) |
| **201** | Created | Recurso creado (POST) |
| **202** | Accepted | Procesamiento asíncrono (subida foto) |
| **400** | Bad Request | JSON inválido, parámetros malformados |
| **401** | Unauthorized | Token inválido, expirado o faltante |
| **403** | Forbidden | Sin permisos (política, bloqueo, API key) |
| **404** | Not Found | Recurso no existe o no visible |
| **409** | Conflict | Recurso duplicado (grant existente) |
| **422** | Unprocessable Entity | Error de validación de datos |
| **429** | Too Many Requests | Rate limit excedido |
| **500** | Internal Server Error | Error del servidor |
| **503** | Service Unavailable | Mantenimiento, DB caída |

---

## Formato de Respuesta de Error

### Estructura Estándar
```json
{
  "error": "Tipo de Error",
  "message": "Descripción legible para humanos",
  "errors": { "campo": ["mensaje específico"] } // solo 422
}
```

### 400 Bad Request
```json
{
  "error": "Bad Request",
  "message": "Invalid JSON payload"
}
```

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Invalid or expired token"
}
```

**Causas comunes:**
- Token Sanctum expirado o revocado
- Token malformado
- Header `Authorization` faltante
- API Key inválido

**Solución:** Re-login o refresh token

### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "You can only delete your own photos"
}
```

**Causas comunes:**
- API Key tipo incorrecto (CLIENT vs ADMIN)
- Usuario no verificado (`verified` middleware)
- Bloqueado por el otro usuario
- Privacy level insuficiente
- Grant requerido pero no concedido
- Contenido expirado (toke, match, post)
- No es participante de la conversación/match/amistad
- Intento de acción sobre recurso ajeno

**Solución:** Verificar permisos, estado de verificación, expiración

### 404 Not Found
```json
{
  "error": "Not found",
  "message": "This photo is no longer available"
}
```

**Causas:**
- Recurso eliminado (soft delete)
- Recurso expirado y limpiado
- Recurso nunca existió
- Usuario bloqueado (oculta recurso)

### 409 Conflict
```json
{
  "error": "Conflict",
  "message": "Grant already exists for this user"
}
```

**Causas:**
- Grant duplicado para mismo campo/usuario
- Match ya existe entre usuarios
- Conversación ya existe

### 422 Unprocessable Entity (Validación)
```json
{
  "error": "Validation failed",
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required.",
      "The email must be a valid email address."
    ],
    "password": [
      "The password must be at least 8 characters.",
      "The password confirmation does not match."
    ],
    "receiver_id": [
      "The receiver id must be a valid UUID.",
      "The selected receiver id is invalid."
    ]
  }
}
```

**Campos comunes de validación:**

| Campo | Reglas típicas |
|-------|----------------|
| `email` | required, email, max:255, unique (register) |
| `password` | required, min:8, confirmed |
| `receiver_id` / `addressee_id` | required, uuid, exists:users,id |
| `visibility` | required, in:PUBLIC,MATCH,FRIENDS,PRIVATE |
| `content` | required, string, max:5000 (posts/messages) |
| `latitude` / `longitude` | required, numeric, between:-90,90 / -180,180 |
| `expires_at` | date, after:now, before_or_equal:+24h (posts) |

### 429 Too Many Requests
```json
{
  "error": "Too Many Requests",
  "message": "Too many attempts. Please try again later.",
  "retry_after": 60
}
```

**Headers de respuesta:**
```
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 0
Retry-After: 60
```

**Límites por endpoint:**

| Endpoint/Grupo | Límite | Ventana |
|----------------|--------|---------|
| Register | 5 | 1 min |
| Login | 10 | 1 min |
| Password Reset | 3 | 1 min |
| Install | 10 | 1 hora |
| Tokes (create/consume) | 30 | 1 min |
| Posts (create) | 30 | 1 min |
| Photos (upload) | 30 | 1 min |
| Messages | 60 | 1 min |
| Conversations (create) | 30 | 1 min |
| General API | 60 | 1 min |
| Discovery | 60 | 1 min |

**Estrategia de reintento:**
```javascript
async function requestWithRetry(url, options, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    const response = await fetch(url, options);
    
    if (response.status !== 429) return response;
    
    const retryAfter = response.headers.get('Retry-After') || Math.pow(2, i) * 30;
    await new Promise(r => setTimeout(r, retryAfter * 1000));
  }
  throw new Error('Max retries exceeded');
}
```

### 500 Internal Server Error
```json
{
  "error": "Server Error",
  "message": "An unexpected error occurred. Please check server logs."
}
```

**Causas:**
- Excepción no controlada
- Error de base de datos
- Error de servicio externo (S3, email)
- Bug en código

**Solución:** Reportar a soporte con timestamp y request ID

### 503 Service Unavailable
```json
{
  "error": "Service Unavailable",
  "message": "System under maintenance. Please try again later."
}
```

---

## Errores Específicos por Dominio

### Autenticación

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Invalid credentials` | 401 | Email o contraseña incorrectos | Login fallido |
| `Email not verified` | 403 | Please verify your email first | Usuario no verificó email |
| `Verification required` | 403 | This action requires a verified account | `verified` middleware |
| `API Key type mismatch` | 403 | Required: CLIENT, Got: ADMIN | API Key incorrecto |
| `Token expired` | 401 | Your session has expired | Token Sanctum expirado |

### Tokes

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Cannot send toke to yourself` | 403 | You cannot send a toke to yourself | Mismo usuario |
| `User already has active toke` | 409 | You already sent a toke to this user | Toke ACTIVE existente |
| `User blocked you` | 403 | Cannot send toke to this user | Bloqueo mutuo |
| `Toke expired` | 422 | This toke is no longer active | >48h o consumido |
| `Not the receiver` | 403 | You are not the receiver of this toke | Consumir toke ajeno |
| `Match already exists` | 409 | Match already exists between users | Match previo activo |

### Matches

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Match expired` | 422 | This match has expired | >7 días |
| `Match not active` | 422 | This match is no longer active | ENDED |
| `Cannot convert expired match` | 422 | Cannot convert an expired match | Match expirado |

### Amistades

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Already friends` | 409 | You are already friends with this user | Amistad ACTIVE |
| `Friendship request pending` | 409 | Friendship request already pending | Request PENDING |
| `Request expired` | 422 | This request has expired | >7 días |
| `Not the addressee` | 403 | You are not the addressee | Aceptar request ajeno |
| `Cannot friend yourself` | 403 | Cannot send friendship request to yourself | Mismo usuario |

### Bloqueos

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Already blocked` | 409 | User already blocked | Bloque existente |
| `Cannot block yourself` | 403 | Cannot block yourself | Mismo usuario |
| `Cannot unblock` | 403 | You can only unblock users you blocked | No es el bloqueador |

### Conversaciones/Mensajes

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Conversation ended` | 422 | This conversation has ended | Status ENDED |
| `Not participant` | 403 | You are not part of this conversation | Usuario ajeno |
| `Cannot message blocked user` | 403 | Cannot start conversation with blocked user | Bloqueo |
| `Message too long` | 422 | Content exceeds 5000 characters | >5000 chars |

### Fotos

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Photo limit reached` | 422 | Maximum 10 photos allowed | Límite usuario |
| `Invalid file type` | 422 | Only JPEG, PNG, WebP allowed | Tipo archivo |
| `File too large` | 422 | Maximum file size 10MB | >10MB |
| `Photo not active` | 422 | This photo is not active | DELETED/PROCESSING |

### Posts

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Post expired` | 422 | This post has expired | >expires_at |
| `Post not active` | 422 | This post is no longer available | DELETED |
| `Expiration too long` | 422 | Posts cannot exceed 24 hours | expires_at > +24h |

### Descubrimiento

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Location required` | 422 | Profile must have location for nearby | Sin ubicación |
| `Profile not discoverable` | 403 | User is not discoverable | discoverable=false |

### Perfil / Grants

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Field not found` | 404 | Profile field not found | slug inexistente |
| `Field not active` | 422 | Field is not active | is_active=false |
| `Cannot grant to owner` | 403 | Cannot grant access to field owner | Dueño del campo |
| `Grant exists` | 409 | Grant already exists | Duplicado |
| `Grant not found` | 404 | Grant not found | Revocar inexistente |

### Verificación Identidad

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Already verified` | 422 | User is already verified | verification_status=verified |
| `Request pending` | 409 | Verification request already pending | status=PENDING |
| `Cannot request verification` | 403 | Verification not available | Config deshabilitada |

### Admin

| Error | Código | Mensaje | Causa |
|-------|--------|---------|-------|
| `Admin required` | 403 | Administrative privileges required | Sin rol admin |
| `Cannot change own role` | 403 | Cannot change your own role | Auto-cambio |
| `Zone not found` | 404 | Geo zone not found | ID inexistente |
| `Polygon not in zone` | 404 | Polygon not found in zone | Mismatch zone_id |

---

## Códigos de Error Internos (para logs)

| Código | Descripción |
|--------|-------------|
| `AUTH_001` | Token Sanctum inválido |
| `AUTH_002` | API Key revocado |
| `AUTH_003` | API Key expirado |
| `AUTHZ_001` | Bloqueo detectado |
| `AUTHZ_002` | Privacy level insuficiente |
| `AUTHZ_003` | Grant requerido |
| `AUTHZ_004` | Verificación requerida |
| `AUTHZ_005` | Recurso expirado |
| `VALID_001` | Error validación email |
| `VALID_002` | Error validación UUID |
| `BIZ_001` | Límite tokes alcanzado |
| `BIZ_002` | Límite fotos alcanzado |
| `BIZ_003` | Match mutuo detectado |
| `SYS_001` | Error conexión DB |
| `SYS_002` | Error almacenamiento S3 |
| `SYS_003` | Error cola jobs |
| `SYS_004` | Error WebSocket Reverb |

---

## Debugging

### Headers Útiles
```
X-Request-ID: uuid          # Para tracing en logs
X-RateLimit-Limit: 30       # Límite actual
X-RateLimit-Remaining: 15   # Quedan
Retry-After: 60             # Segundos para reintentar (429)
```

### Logs del Servidor
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Filtrar por request ID
grep "X-Request-ID: abc123" storage/logs/laravel.log
```

### Modo Debug (solo desarrollo)
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## Checklist de Manejo de Errores (Cliente)

- [ ] **401**: Refresh token → si falla, logout + redirect login
- [ ] **403**: Mostrar mensaje amigable según `message`, no exponer detalles internos
- [ ] **422**: Mostrar errores de campo en formulario (`errors.campo[0]`)
- [ ] **429**: Esperar `Retry-After` + backoff exponencial, mostrar contador
- [ ] **500/503**: Mostrar "Error temporal, reintente en unos minutos", loguear para soporte
- [ ] **Network error**: Reintentar 3x con backoff, luego mostrar offline
- [ ] **Timeout**: Aumentar timeout para uploads (fotos), mostrar progreso