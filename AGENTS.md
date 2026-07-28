# Altobul - Agent Instructions

## Project Status
**Active Development** - Backend implemented with Laravel 12, PHP 8.3. Database schema complete with migrations. Tests passing.

## Tech Stack
- **Frontend**: Next.js, React, TypeScript, Tailwind CSS (planned)
- **Backend**: Laravel 12, PHP 8.3, API, WebSockets (Laravel Reverb)
- **Database**: PostgreSQL + PostGIS (geospatial)
- **Cache/Queue**: Redis
- **Storage**: S3-compatible object storage
- **Maps**: Interactive admin map with PostGIS polygons
- **Static Analysis**: PHPStan Level 5 (Larastan)
- **Code Style**: Laravel Pint (PSR-12)

## Key Domain Concepts
- **Progressive interaction**: Descubrir → Toke → Match (7 days) → Amistad (permanent)
- **Privacy levels**: Público / Match / Amigos / Privado (+ optional "verified only")
- **Verification**: Optional, 3 states (No verificado / Pendiente / Verificado)
- **Geo-zones**: Admin-defined polygons via PostGIS
- **Content TTL**: Toke 48h, Match 7d, Posts 24h, Amistad permanent

## Architecture
- **Multi-app**: Backend (API + Auth + Admin) + Client App + Admin App (separately deployable)
- **API Key auth**: Application identity via X-API-Key header
- **User auth**: Sanctum tokens via Authorization: Bearer
- **Authorization**: Centralized AuthorizationService with privacy, relationships, blocks, verification, grants, expiration
- **Real-time**: Laravel Reverb WebSocket server with private channels

## Dev Commands

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan test
php vendor/bin/pint --test
composer analyse
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh with seed
php artisan migrate:fresh --seed

# Rollback
php artisan migrate:rollback
```

### Tests
```bash
# All tests
php artisan test

# Single test class
php artisan test --filter=ApiKeyMiddlewareTest

# Single test method
php artisan test --filter=test_client_key_cannot_access_admin_endpoint
```

### Lint/Style
```bash
php vendor/bin/pint
php vendor/bin/pint --test
```

### Static Analysis
```bash
composer analyse
```

### Queue Workers
```bash
# Database queue (development)
php artisan queue:listen --tries=3 --timeout=60

# Redis queue (production)
php artisan queue:work redis --tries=3 --timeout=60 --backoff=10,30,60

# With Supervisor (production)
# See config/supervisor.conf
```

### Reverb (WebSockets)
```bash
# Install & generate keys
php artisan reverb:install

# Start server
php artisan reverb:start --host=0.0.0.0 --port=8080

# Debug mode
php artisan reverb:start --debug
```

## Test Conventions
- Tests in `tests/Feature` and `tests/Unit`
- Uses `RefreshDatabase` trait
- Factory pattern with `Str::uuid()` for IDs
- PostgreSQL in-memory for testing (configured in phpunit.xml)

## Key Files

### API Keys
- Model: `app/Models/ApiKey.php`
- Service: `app/Services/ApiKeyService.php`
- Middleware: `app/Http/Middleware/ApiKeyMiddleware.php`
- Controller: `app/Http/Controllers/Admin/ApiKeyController.php`

### Auth
- Service: `app/Services/Auth/AuthService.php`
- Controller: `app/Http/Controllers/AuthController.php`

### Authorization
- Service: `app/Services/Authorization/AuthorizationService.php`
- Interface: `app/Contracts/AuthorizationServiceInterface.php`
- Domain: `app/Domain/Authorization/`

### Installer
- Controller: `app/Http/Controllers/InstallController.php`
- Routes: `/api/install`, `/api/install/status`

### Geo
- Service: `app/Services/Geo/GeoZoneService.php`
- Models: `GeoZone`, `GeoPolygon`

### Social Domain
- Models: `Toke`, `UserMatch`, `Friendship`, `FriendshipRequest`, `Block`, `Conversation`, `Message`
- Policies: `TokePolicy`, `UserMatchPolicy`, `FriendshipPolicy`, `FriendshipRequestPolicy`, `BlockPolicy`, `ConversationPolicy`

### Profile Field Grants
- Model: `app/Models/ProfileFieldValueAccess.php`
- Controller: `app/Http/Controllers/Profile/ProfileFieldValueAccessController.php`
- Policy: `app/Policies/ProfileFieldValueAccessPolicy.php`
- Events: `app/Events/Broadcast/NewGrant.php`

### WebSocket Events
- `app/Events/Broadcast/NewMessage.php`
- `app/Events/Broadcast/NewMatch.php`
- `app/Events/Broadcast/NewFriendship.php`
- `app/Events/Broadcast/NewTokeReceived.php`
- `app/Events/Broadcast/NewGrant.php`
- Channel auth: `routes/channels.php`

### Queue Configuration
- Config: `config/queue.php` (database, redis, failover)
- Failed jobs: `database-uuids` driver

### Reverb Configuration
- Config: `config/reverb.php`
- Broadcasting: `config/broadcasting.php`

## CI/CD
- GitHub Actions: `.github/workflows/ci.yml`
- Runs tests, lint, and static analysis on push/PR

## Important Patterns
1. **API Key ≠ User**: API Key identifies application (Client/Admin), User Token identifies person
2. **Admin routes protected by both API Key + Admin middleware**
3. **AuthorizationService is single source of truth for privacy/access**
4. **Self-view allowed, but expiration still checked for time-limited resources**
5. **Blocks are absolute - override all other permissions**
6. **Privacy: PUBLIC > MATCH > FRIENDS > PRIVATE (with grants)**
7. **Expiration checked for: posts, tokes, matches**
8. **Broadcast events dispatched after DB transaction commits**

## For Future Development
- Client App installation: `/api/install` → generate API keys → configure Client with backend URL + Client API Key
- Admin App installation: same but with Admin API Key
- Health check: `GET /api/system/health`

## New Endpoints (v1.1)

### Profile Field Grants (PRIVATE fields only)
```
GET  /api/client/profile/fields/{fieldValue}/grants
POST /api/client/profile/fields/{fieldValue}/grants
DELETE /api/client/profile/fields/{fieldValue}/grants/{grantee}
```

### Real-time Events
| Event | Channel | Description |
|-------|---------|-------------|
| `toke.received` | `user.{receiverId}` | New toke received |
| `match.created` | `user.{userA}`, `user.{userB}` | Mutual toke created match |
| `friendship.created` | `user.{userA}`, `user.{userB}` | Friendship established |
| `message.created` | `conversation.{conversationId}` | New message in conversation |
| `grant.created` | `user.{granteeId}` | Access granted to private field |