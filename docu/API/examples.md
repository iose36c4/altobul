# Ejemplos de Integración

## Configuración Inicial

### 1. Verificar si el backend está instalado
```bash
curl -X GET https://api.example.com/api/install/status
```

### 2. Instalar backend (primera vez)
```bash
curl -X POST https://api.example.com/api/install \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@miapp.com",
    "password": "seguro123",
    "password_confirmation": "seguro123",
    "app_name": "Mi Aplicación"
  }'
```

### 3. Login como admin y crear API Key CLIENT
```bash
# Login admin
ADMIN_TOKEN=$(curl -s -X POST https://api.example.com/api/admin/auth/login \
  -H "X-API-Key: <admin-api-key>" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@miapp.com","password":"seguro123"}' | jq -r .token)

# Crear API Key para cliente
curl -X POST https://api.example.com/api/admin/api-keys \
  -H "X-API-Key: <admin-api-key>" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mi App Web",
    "type": "CLIENT",
    "expires_in_days": 365
  }'
```

**Respuesta:** Guarda el `raw_key` - **solo se muestra una vez**.

---

## Flujo Completo de Usuario

### 1. Registro de usuario
```bash
curl -X POST https://api.example.com/api/client/auth/register \
  -H "X-API-Key: <client-api-key>" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "mipassword123",
    "password_confirmation": "mipassword123",
    "device_name": "Mi App Web"
  }'
```

### 2. Login
```bash
LOGIN_RESPONSE=$(curl -s -X POST https://api.example.com/api/client/auth/login \
  -H "X-API-Key: <client-api-key>" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@example.com",
    "password": "mipassword123",
    "device_name": "Mi App Web"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | jq -r .token)
USER_ID=$(echo $LOGIN_RESPONSE | jq -r .user.id)
```

### 3. Verificar email (simular click en link)
```bash
# El link llega por email: /api/client/auth/verify-email/{id}/{hash}
curl -X GET "https://api.example.com/api/client/auth/verify-email/$USER_ID/$HASH" \
  -H "X-API-Key: <client-api-key>"
```

### 4. Completar perfil
```bash
curl -X PUT https://api.example.com/api/client/profile \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Desarrollador Full Stack",
    "description": "Me gusta crear apps",
    "birth_date": "1990-05-15",
    "gender": "male",
    "looking_for": "female",
    "min_age": 25,
    "max_age": 35,
    "max_distance_km": 30,
    "discoverable": true,
    "profile_visibility": "PUBLIC"
  }'
```

### 5. Establecer ubicación
```bash
curl -X PUT https://api.example.com/api/client/profile/location \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": -34.6037,
    "longitude": -58.3816,
    "precision_meters": 500
  }'
```

### 6. Subir foto de perfil
```bash
curl -X POST https://api.example.com/api/client/photos \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -F "photo=@/ruta/foto.jpg" \
  -F "visibility=PUBLIC" \
  -F "requires_verified=false"
```

### 7. Descubrir usuarios
```bash
curl -X GET "https://api.example.com/api/client/discover?limit=10&verified_only=true" \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### 8. Enviar Toke a un usuario
```bash
curl -X POST https://api.example.com/api/client/tokes \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"receiver_id": "uuid-del-usuario-interesante"}'
```

### 9. Ver tokes recibidos
```bash
curl -X GET https://api.example.com/api/client/tokes \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### 10. Consumir toke recibido (crea match si mutuo)
```bash
curl -X POST https://api.example.com/api/client/tokes/{toke-id}/consume \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### 11. Ver matches
```bash
curl -X GET https://api.example.com/api/client/matches \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### 12. Convertir match en amistad
```bash
curl -X POST https://api.example.com/api/client/matches/{match-id}/convert-to-friendship \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### 13. Crear conversación y enviar mensaje
```bash
# Crear conversación
CONV=$(curl -s -X POST https://api.example.com/api/client/conversations \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"recipient_id": "uuid-del-amigo"}')

CONV_ID=$(echo $CONV | jq -r .conversation.id)

# Enviar mensaje
curl -X POST https://api.example.com/api/client/conversations/$CONV_ID/messages \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "¡Hola! Qué tal?"}'
```

---

## Flujo de Verificación de Identidad

### Solicitar verificación
```bash
curl -X POST https://api.example.com/api/client/auth/verification/request \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "verification_method": "document",
    "external_reference": "DNI-12345678"
  }'
```

### Verificar estado
```bash
curl -X GET https://api.example.com/api/client/auth/verification/status \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Flujo de Amistad Directa (sin match)

```bash
# Solicitar amistad
curl -X POST https://api.example.com/api/client/friendships \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"addressee_id": "uuid-del-usuario"}'

# Ver solicitudes recibidas
curl -X GET https://api.example.com/api/client/friendship-requests \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"

# Aceptar solicitud
curl -X POST https://api.example.com/api/client/friendship-requests/{request-id}/accept \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Campos Personalizados (Profile Fields)

### Listar definiciones disponibles
```bash
curl -X GET https://api.example.com/api/client/profile/fields \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### Establecer valor (ej. campo "intereses" tipo multi-select)
```bash
curl -X PUT https://api.example.com/api/client/profile/fields/intereses \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "value": "",
    "selected_options": ["programacion", "musica", "deportes"]
  }'
```

### Otorgar acceso a campo PRIVATE
```bash
# Obtener field_value_id primero
FIELD_VALUE_ID=$(curl -s -X GET https://api.example.com/api/client/profile/fields/telefono \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" | jq -r .value.id)

# Otorgar acceso a un amigo
curl -X POST https://api.example.com/api/client/profile/fields/$FIELD_VALUE_ID/grants \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "grantee_id": "uuid-del-amigo",
    "expires_at": "2026-12-31T23:59:59Z"
  }'
```

---

## Fotos y Posts Privados con Grants

### Subir foto PRIVATE
```bash
curl -X POST https://api.example.com/api/client/photos \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -F "photo=@/ruta/foto-privada.jpg" \
  -F "visibility=PRIVATE" \
  -F "requires_verified=true"
```

### Otorgar acceso a foto
```bash
curl -X POST https://api.example.com/api/client/photos/{photo-id}/grants \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"grantee_id": "uuid-del-amigo"}'
```

### Crear post PRIVATE
```bash
curl -X POST https://api.example.com/api/client/posts \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Contenido solo para amigos cercanos",
    "visibility": "PRIVATE",
    "requires_verified": false,
    "expires_at": "2026-07-29T20:00:00Z"
  }'
```

---

## Descubrimiento Avanzado

### Cercanos (requiere ubicación en perfil)
```bash
curl -X GET "https://api.example.com/api/client/discover/nearby?limit=20&verified_only=true" \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### Online ahora
```bash
curl -X GET "https://api.example.com/api/client/discover/online?limit=10" \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

### Con filtros combinados
```bash
curl -X GET "https://api.example.com/api/client/discover?verified_only=true&order=compatibility&fields=profile,photos&limit=15" \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Administración (Solo admins)

### Listar usuarios con filtros
```bash
curl -X GET "https://api.example.com/api/admin/users?status=active&verification_status=verified&per_page=50" \
  -H "X-API-Key: <admin-api-key>" \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

### Suspender usuario
```bash
curl -X POST https://api.example.com/api/admin/users/{user-id}/suspend \
  -H "X-API-Key: <admin-api-key>" \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

### Crear geo-zona
```bash
curl -X POST https://api.example.com/api/admin/geo-zones \
  -H "X-API-Key: <admin-api-key>" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Zona Norte",
    "description": "Barrios del norte",
    "is_active": true,
    "polygons": [{
      "name": "Polígono Norte",
      "geometry": {
        "type": "Polygon",
        "coordinates": [[
          [-58.5, -34.5],
          [-58.4, -34.5],
          [-58.4, -34.4],
          [-58.5, -34.4],
          [-58.5, -34.5]
        ]]
      },
      "sort_order": 0
    }]
  }'
```

### Aprobar verificación
```bash
curl -X POST https://api.example.com/api/admin/verification-requests/{request-id}/approve \
  -H "X-API-Key: <admin-api-key>" \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

---

## WebSockets (Tiempo Real)

### Conectar a Reverb (JavaScript)
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'reverb-app-key',
  wsHost: 'api.example.com',
  wsPort: 8080,
  wssPort: 443,
  forceTLS: true,
  authEndpoint: '/api/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${userToken}`,
      'X-API-Key': '<client-api-key>'
    }
  }
});

// Escuchar tokes recibidos
echo.private(`user.${userId}`)
  .listen('.toke.received', (e) => {
    console.log('Nuevo toke:', e.toke);
    // Actualizar UI
  });

// Escuchar matches
echo.private(`user.${userId}`)
  .listen('.match.created', (e) => {
    console.log('¡Nuevo match!', e.match);
  });

// Escuchar mensajes
echo.private(`conversation.${conversationId}`)
  .listen('.message.created', (e) => {
    console.log('Nuevo mensaje:', e.message);
  });

// Escuchar grants
echo.private(`user.${userId}`)
  .listen('.grant.created', (e) => {
    console.log('Nuevo acceso concedido:', e.grant);
  });
```

### Autenticación de canales
```bash
# El cliente debe hacer POST a /api/broadcasting/auth
# con el token de usuario para obtener firma del canal
```

---

## Manejo de Errores Comunes

### Token expirado (401)
```bash
# Refrescar token
curl -X POST https://api.example.com/api/client/auth/refresh \
  -H "X-API-Key: <client-api-key>" \
  -H "Authorization: Bearer $OLD_TOKEN"
```

### Rate limited (429)
```bash
# Esperar y reintentar con backoff exponencial
# Header: Retry-After: 60
sleep 60
# Reintentar request
```

### Validación fallida (422)
```json
{
  "error": "Validation failed",
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Sin permisos (403)
```json
{
  "error": "Forbidden",
  "message": "You can only delete your own photos"
}
```

---

## Buenas Prácticas

1. **Siempre usa HTTPS** en producción
2. **Almacena tokens seguros** (Keychain/Keystore, no localStorage)
3. **Implementa retry con backoff** para 429 y 5xx
4. **Maneja expiración de tokens** proactivamente
5. **Valida respuestas** antes de usar datos
6. **Usa paginación** en listados grandes
7. **Registra dispositivos** en login (`device_name`)
8. **Revoca tokens** en logout
9. **Monitorea health check** (`/api/system/health`)
10. **Prueba en staging** antes de producción