# Authentication & Security

## API Key Authentication

Every request must include an **API Key** header identifying the application:

```
X-API-Key: <your-api-key>
```

### API Key Types

| Type | Prefix | Use Case |
|------|--------|----------|
| `CLIENT` | `altobul_cli_` | End-user applications (web, mobile) |
| `ADMIN` | `altobul_adm_` | Administrative panels |
| `MOBILE` | `altobul_mob_` | Native mobile apps |
| `INTEGRATION` | `altobul_int_` | Third-party integrations |

### Obtaining API Keys

Admin users create keys via:
```
POST /api/admin/api-keys
Authorization: Bearer <admin-token>
X-API-Key: <admin-api-key>
```

Response includes `raw_key` **only once** - store securely.

## User Authentication (Sanctum)

After API Key validation, user endpoints require a **Sanctum token**:

```
Authorization: Bearer <sanctum-token>
```

### Login Flow

```
POST /api/client/auth/login
X-API-Key: <client-api-key>
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "securepassword",
  "device_name": "My App"
}
```

**Response (200):**
```json
{
  "user": { "id": "...", "email": "...", "role": "user", "verification_status": "verified" },
  "token": "1|abc123...",
  "expires_at": "2026-08-04T10:30:00.000000Z"
}
```

Token format: `{id}|{token}` - store full string.

### Registration Flow

```
POST /api/client/auth/register
X-API-Key: <client-api-key>
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "securepassword",
  "password_confirmation": "securepassword",
  "device_name": "My App"
}
```

**Response (201):**
```json
{
  "user": { "id": "...", "email": "...", "role": "user", "verification_status": "not_verified" },
  "message": "Registration successful. Please verify your email."
}
```

Email verification required before most actions.

### Token Refresh

```
POST /api/client/auth/refresh
Authorization: Bearer <token>
X-API-Key: <client-api-key>
```

Returns new token with extended expiry.

### Logout

```
POST /api/client/auth/logout
Authorization: Bearer <token>
X-API-Key: <client-api-key>
```

Revokes current token.

### Get Current User

```
GET /api/client/auth/me
Authorization: Bearer <token>
X-API-Key: <client-api-key>
```

## Email Verification

### Send Verification Email

```
POST /api/client/auth/resend-verification
Authorization: Bearer <token>
X-API-Key: <client-api-key>
```

### Verify Email (via link)

```
GET /api/client/auth/verify-email/{id}/{hash}
X-API-Key: <client-api-key>
```

Link sent via email. No user token required.

### Check Verification Status

```
GET /api/client/auth/verification/status
Authorization: Bearer <token>
X-API-Key: <client-api-key>
```

## Password Reset

### Request Reset Link

```
POST /api/client/auth/forgot-password
X-API-Key: <client-api-key>
Content-Type: application/json

{ "email": "user@example.com" }
```

### Reset Password

```
POST /api/client/auth/reset-password
X-API-Key: <client-api-key>
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "newpassword",
  "password_confirmation": "newpassword",
  "token": "reset-token-from-email"
}
```

## Identity Verification (Profile Verification)

Optional verification for trust indicators.

### Request Verification

```
POST /api/client/auth/verification/request
Authorization: Bearer <token>
X-API-Key: <client-api-key>
Content-Type: application/json

{
  "verification_method": "document", // or "video", "manual"
  "external_reference": "REF123456" // optional
}
```

**Methods:**
- `document` - Government ID upload
- `video` - Video call verification
- `manual` - Admin manual review

### Check Verification Status

```
GET /api/client/auth/verification/status
Authorization: Bearer <token>
X-API-Key: <client-api-key>
```

**Response:**
```json
{
  "verification": {
    "id": "...",
    "status": "pending", // not_verified, pending, verified, rejected
    "verification_method": "document",
    "submitted_at": "2026-07-28T10:00:00.000000Z",
    "rejection_reason": null
  }
}
```

## Admin Authentication

Admin routes require **both** API Key (ADMIN type) and Admin user token:

```
X-API-Key: <admin-api-key>
Authorization: Bearer <admin-sanctum-token>
```

Admin user must have `role: admin`.

### Admin Login

```
POST /api/admin/auth/login
X-API-Key: <admin-api-key>
Content-Type: application/json

{ "email": "admin@example.com", "password": "..." }
```

### Admin Routes Protection

All `/api/admin/*` routes (except auth) require:
1. Valid ADMIN API Key
2. Valid Sanctum token
3. User with `role: admin`

Middleware chain: `api.key:ADMIN` → `auth:sanctum` → `admin`

## Security Best Practices

### Client Applications

1. **Store API Key securely** - Never expose in frontend code
2. **Use HTTPS only** - Enforce TLS 1.2+
3. **Token storage** - Secure storage (Keychain/Keystore, not localStorage)
4. **Token expiry** - Handle 401 by refreshing or re-login
5. **Rate limiting** - Implement exponential backoff on 429

### API Key Management

- Rotate keys periodically
- Set expiration dates (`expires_in_days`)
- Revoke compromised keys immediately
- Use different keys per environment (dev/staging/prod)

### CORS Configuration

Configure `config/cors.php` for your client domains:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://app.example.com', 'https://admin.example.com'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

### Webhook Security (if applicable)

For webhook endpoints, verify signatures:
```
X-Webhook-Signature: sha256=...
```

## Error Responses

### 401 Unauthorized

```json
{
  "error": "Unauthorized",
  "message": "Invalid or expired token"
}
```

### 403 Forbidden

```json
{
  "error": "Forbidden",
  "message": "API Key type mismatch. Required: CLIENT"
}
```

### 422 Validation Error

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

### 429 Rate Limited

```json
{
  "error": "Too Many Requests",
  "message": "Too many attempts. Please try again later.",
  "retry_after": 60
}
```