# Documentación API Altobul - Índice

Bienvenido a la documentación completa de la API de Altobul.

## 📚 Archivos de Documentación

| Archivo | Descripción |
|---------|-------------|
| [`intro.md`](intro.md) | Visión general, arquitectura, conceptos clave, autenticación dual |
| [`auth.md`](auth.md) | Autenticación y seguridad: API Keys, Sanctum, flujo login/register, verificación |
| [`endpoints.md`](endpoints.md) | Listado completo de todos los endpoints con parámetros y respuestas |
| [`examples.md`](examples.md) | Ejemplos prácticos de integración (bash, JavaScript) |
| [`errors.md`](errors.md) | Manejo de errores, códigos de estado, troubleshooting |

---

## 🚀 Inicio Rápido

### 1. Verificar instalación
```bash
curl https://api.example.com/api/install/status
```

### 2. Instalar backend (primera vez)
```bash
curl -X POST https://api.example.com/api/install \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@miapp.com","password":"seguro123","password_confirmation":"seguro123"}'
```

### 3. Crear API Key CLIENT (como admin)
```bash
# Login admin
TOKEN=$(curl -s -X POST https://api.example.com/api/admin/auth/login \
  -H "X-API-Key: <admin-key>" \
  -d '{"email":"admin@miapp.com","password":"seguro123"}' | jq -r .token)

# Crear key cliente
curl -X POST https://api.example.com/api/admin/api-keys \
  -H "X-API-Key: <admin-key>" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Mi App","type":"CLIENT","expires_in_days":365}'
```

### 4. Registrar usuario en tu app
```bash
curl -X POST https://api.example.com/api/client/auth/register \
  -H "X-API-Key: <client-key>" \
  -d '{"email":"user@email.com","password":"pass123","password_confirmation":"pass123"}'
```

### 5. Login y obtener token
```bash
TOKEN=$(curl -s -X POST https://api.example.com/api/client/auth/login \
  -H "X-API-Key: <client-key>" \
  -d '{"email":"user@email.com","password":"pass123"}' | jq -r .token)
```

### 6. Usar API autenticada
```bash
curl -H "X-API-Key: <client-key>" \
     -H "Authorization: Bearer $TOKEN" \
     https://api.example.com/api/client/profile
```

---

## 🔑 Conceptos Clave

### Flujo de Interacción
```
Descubrir → Toke (48h) → Match (7d) → Amistad (permanente)
                    ↓
            Amistad directa (7d request)
```

### Niveles de Privacidad
| Nivel | Quién ve |
|-------|----------|
| `PUBLIC` | Todos |
| `MATCH` | Matches activos |
| `FRIENDS` | Amigos confirmados |
| `PRIVATE` | Solo con Grant explícito |
| `+ verified_only` | Solo usuarios verificados |

### Tipos de API Key
| Tipo | Prefijo | Uso |
|------|---------|-----|
| `CLIENT` | `altobul_cli_` | Apps de usuarios |
| `ADMIN` | `altobul_adm_` | Paneles admin |
| `MOBILE` | `altobul_mob_` | Apps nativas |
| `INTEGRATION` | `altobul_int_` | Integraciones 3rd party |

### TTL Contenido
| Contenido | Duración |
|-----------|----------|
| Toke | 48 horas |
| Match | 7 días |
| Post | 24 horas (configurable) |
| Solicitud Amistad | 7 días |
| Amistad | Permanente |

---

## 🌐 WebSockets (Tiempo Real)

**Servidor:** Laravel Reverb  
**Canales privados** con autenticación Sanctum

| Evento | Canal | Descripción |
|--------|-------|-------------|
| `toke.received` | `user.{id}` | Nuevo toke recibido |
| `match.created` | `user.{id}` | Match mutuo creado |
| `friendship.created` | `user.{id}` | Amistad confirmada |
| `message.created` | `conversation.{id}` | Nuevo mensaje |
| `grant.created` | `user.{id}` | Acceso concedido a campo/foto/post privado |

```javascript
// Ejemplo conexión
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'reverb-key',
  wsHost: 'api.example.com',
  wsPort: 8080,
  forceTLS: true,
  authEndpoint: '/api/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${userToken}`,
      'X-API-Key': '<client-key>'
    }
  }
});

echo.private(`user.${userId}`)
  .listen('.toke.received', (e) => console.log('Nuevo toke:', e.toke));
```

---

## 📋 Checklist de Integración

### Pre-producción
- [ ] Backend instalado y health check OK
- [ ] API Key CLIENT creada y guardada segura
- [ ] CORS configurado para tu dominio
- [ ] HTTPS obligatorio en producción
- [ ] Rate limiting probado (429 handling)
- [ ] Token refresh implementado
- [ ] Logout revoca token
- [ ] WebSocket conecta y recibe eventos

### Manejo de Errores
- [ ] 401 → refresh token → retry → logout
- [ ] 403 → mensaje usuario según `message`
- [ ] 422 → mostrar errores de campo en form
- [ ] 429 → backoff exponencial + Retry-After
- [ ] 5xx → reintentar 3x → mostrar error genérico

### Seguridad
- [ ] API Key solo en backend/server-side
- [ ] Tokens en secure storage (Keychain/Keystore)
- [ ] Validación certificados TLS
- [ ] Logs sin tokens ni PII

---

## 📞 Soporte

- **Health Check:** `GET /api/system/health`
- **Compatibilidad:** `GET /api/system/compatibility`
- **Logs:** `storage/logs/laravel.log` (servidor)
- **Request ID:** Header `X-Request-ID` para tracing

---

## 📝 Versión

**v1.1** - Julio 2026
- Profile Field Grants (campos PRIVATE)
- Photo/Post Grants
- Real-time Events (WebSockets)
- Discovery avanzado (nearby, online, recent)
- Geo-zonas admin (PostGIS)
- Verificación identidad (document/video/manual)

---

*Documentación generada automáticamente desde el código fuente.*