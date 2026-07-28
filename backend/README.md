# Altobul Backend

Backend API for Altobul - A geosocial application built with Laravel 12, PostgreSQL + PostGIS.

## Tech Stack

- **Framework**: Laravel 12 (PHP 8.3)
- **Database**: PostgreSQL 16 + PostGIS 3.4
- **Cache/Queue**: Redis 7
- **Authentication**: Laravel Sanctum (API tokens) + API Keys (application identity)
- **Real-time**: Laravel Reverb (WebSockets)
- **Storage**: S3-compatible (MinIO / AWS S3)
- **Testing**: PHPUnit 12, PostgreSQL in-memory
- **Code Style**: Laravel Pint (PSR-12)
- **Static Analysis**: PHPStan Level 5 (Larastan)

## Architecture

### Multi-App Design
- **Backend**: API + Auth + Admin panel (this repo)
- **Client App**: Separate deployable frontend (React Native / Next.js)
- **Admin App**: Separate deployable admin dashboard

### Authentication Model
- **API Keys** (`X-API-Key`): Identify the application (CLIENT vs ADMIN)
- **Sanctum Tokens** (`Authorization: Bearer`): Identify the user
- Both required for protected endpoints

### Key Domain Concepts
- **Progressive Interaction**: Descubrir → Toke (48h) → Match (7d) → Amistad (permanent)
- **Privacy Levels**: PUBLIC > MATCH > FRIENDS > PRIVATE (+ optional "verified only")
- **Verification**: Optional, 3 states (not_verified / pending / verified)
- **Geo-zones**: Admin-defined PostGIS polygons for discovery
- **Content TTL**: Toke 48h, Match 7d, Posts 24h, Friendship permanent

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- PostgreSQL 16 + PostGIS 3.4
- Redis 7
- Node.js 20+ (for Vite)

### Installation

```bash
cd backend
composer install
cp .env.example .env
# Configure .env with database, Redis, Reverb credentials
php artisan key:generate
php artisan migrate --force
php artisan test
npm install --ignore-scripts
npm run build
```

### Development

```bash
# Start all services
composer dev

# Or individually:
php artisan serve          # HTTP server
php artisan queue:listen   # Queue worker
php artisan reverb:start   # WebSocket server
npm run dev                # Vite
```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment | `local` |
| `DB_CONNECTION` | Database driver | `sqlite` (dev) / `pgsql` (prod) |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `5432` |
| `DB_DATABASE` | Database name | `altobul` |
| `DB_USERNAME` | Database user | - |
| `DB_PASSWORD` | Database password | - |
| `REDIS_HOST` | Redis host | `127.0.0.1` |
| `REDIS_PORT` | Redis port | `6379` |
| `QUEUE_CONNECTION` | Queue driver | `database` / `redis` |
| `BROADCAST_CONNECTION` | Broadcast driver | `reverb` / `log` |
| `REVERB_APP_ID` | Reverb app ID | - |
| `REVERB_APP_KEY` | Reverb app key | - |
| `REVERB_APP_SECRET` | Reverb app secret | - |
| `REVERB_HOST` | Reverb host | `localhost` |
| `REVERB_PORT` | Reverb port | `8080` |
| `REVERB_SCHEME` | Reverb scheme | `http` |

### Reverb (WebSockets)

```bash
# Generate keys
php artisan reverb:install

# Start server
php artisan reverb:start --host=0.0.0.0 --port=8080

# Or with debug
php artisan reverb:start --debug
```

### Queue Workers

```bash
# Database queue (default)
php artisan queue:listen --tries=3 --timeout=60

# Redis queue (production)
php artisan queue:work redis --tries=3 --timeout=60 --backoff=10,30,60

# With Supervisor (production)
# See docs/supervisor.conf
```

## API Documentation

### Installation
```
GET  /api/install              # Check installation status
GET  /api/install/status       # Detailed health checks
POST /api/install              # Install backend (creates admin + API keys)
```

### Health Check
```
GET /api/system/health
```

### Client API (requires CLIENT API Key)

#### Authentication
```
POST /api/client/auth/register
POST /api/client/auth/login
POST /api/client/auth/logout
POST /api/client/auth/refresh
GET  /api/client/auth/me
POST /api/client/auth/forgot-password
POST /api/client/auth/reset-password
GET  /api/client/auth/verify-email/{id}/{hash}
POST /api/client/auth/resend-verification
POST /api/client/auth/verification/request
GET  /api/client/auth/verification/status
```

#### Profile
```
GET  /api/client/profile
PUT  /api/client/profile
PUT  /api/client/profile/location
GET  /api/client/profile/fields
GET  /api/client/profile/fields/{slug}
PUT  /api/client/profile/fields/{slug}
DELETE /api/client/profile/fields/{slug}

# Field Grants (PRIVATE fields only)
GET  /api/client/profile/fields/{fieldValue}/grants
POST /api/client/profile/fields/{fieldValue}/grants
DELETE /api/client/profile/fields/{fieldValue}/grants/{grantee}
```

#### Discovery & Social
```
GET  /api/client/users/{user}

# Tokes
POST   /api/client/tokes
GET    /api/client/tokes
POST   /api/client/tokes/{toke}/consume
DELETE /api/client/tokes/{toke}

# Matches
GET /api/client/matches
POST /api/client/matches/{match}/convert-to-friendship

# Friendships
GET  /api/client/friendships
POST /api/client/friendships
DELETE /api/client/friendships/{friendship}

# Friendship Requests
GET  /api/client/friendship-requests
POST /api/client/friendship-requests
POST /api/client/friendship-requests/{request}/accept
DELETE /api/client/friendship-requests/{request}

# Blocks
GET  /api/client/blocks
POST /api/client/blocks
DELETE /api/client/blocks/{block}

# Conversations
GET  /api/client/conversations
POST /api/client/conversations
GET  /api/client/conversations/{conversation}
DELETE /api/client/conversations/{conversation}

# Messages
GET  /api/client/conversations/{conversation}/messages
POST /api/client/conversations/{conversation}/messages

# Photos
GET  /api/client/photos
POST /api/client/photos
GET  /api/client/photos/{photo}
DELETE /api/client/photos/{photo}

# Posts
GET  /api/client/posts
POST /api/client/posts
GET  /api/client/posts/{post}
DELETE /api/client/posts/{post}
```

### Admin API (requires ADMIN API Key + admin user token)

```
# Config
GET /api/admin/config
PUT /api/admin/config

# API Keys
GET  /api/admin/api-keys
POST /api/admin/api-keys
GET  /api/admin/api-keys/{apiKey}
DELETE /api/admin/api-keys/{apiKey}

# Profile Fields
GET  /api/admin/profile-fields
POST /api/admin/profile-fields
GET  /api/admin/profile-fields/{field}
PUT  /api/admin/profile-fields/{field}
DELETE /api/admin/profile-fields/{field}
POST /api/admin/profile-fields/reorder

# Verification
GET /api/admin/verification-requests
GET /api/admin/verification-requests/{request}
POST /api/admin/verification-requests/{request}/approve
POST /api/admin/verification-requests/{request}/reject

# Geo Zones
GET  /api/admin/geo-zones
POST /api/admin/geo-zones
GET  /api/admin/geo-zones/{zone}
PUT  /api/admin/geo-zones/{zone}
DELETE /api/admin/geo-zones/{zone}
POST /api/admin/geo-zones/{zone}/polygons
PUT  /api/admin/geo-zones/{zone}/polygons/{polygon}
DELETE /api/admin/geo-zones/{zone}/polygons/{polygon}

# Users
GET  /api/admin/users
GET  /api/admin/users/{user}
POST /api/admin/users/{user}/suspend
POST /api/admin/users/{user}/activate
POST /api/admin/users/{user}/change-role

# Audit Logs
GET /api/admin/audit-logs
```

## Real-time Events (WebSockets)

### Channels
- `user.{userId}` - Private user channel
- `conversation.{conversationId}` - Private conversation channel

### Events

| Event | Channel | Payload |
|-------|---------|---------|
| `toke.received` | `user.{receiverId}` | `{ toke: {...}, sender: {...} }` |
| `match.created` | `user.{userA}`, `user.{userB}` | `{ match: {...}, other_user: {...} }` |
| `friendship.created` | `user.{userA}`, `user.{userB}` | `{ friendship: {...}, other_user: {...} }` |
| `message.created` | `conversation.{conversationId}` | `{ conversation_id, message: {...} }` |
| `grant.created` | `user.{granteeId}` | `{ grant: {...}, field_value: {...} }` |

### Client Connection (JavaScript)

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/api/client/broadcasting/auth',
    auth: {
        headers: {
            'X-API-Key': 'your-client-api-key',
            'Authorization': 'Bearer ' + userToken,
        }
    }
});

// Listen for new messages
echo.private(`conversation.${conversationId}`)
    .listen('message.created', (e) => {
        console.log('New message:', e.message);
    });

// Listen for new tokes
echo.private(`user.${userId}`)
    .listen('toke.received', (e) => {
        console.log('New toke:', e.toke);
    });
```

## Testing

```bash
# Run all tests
php artisan test

# Run with parallel
php artisan test --parallel

# Specific test
php artisan test --filter=AuthorizationMatrixTest

# Lint
php vendor/bin/pint

# Static analysis (if configured)
composer analyse
```

## Deployment

### Docker (Example)

```dockerfile
FROM php:8.3-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

CMD ["php-fpm"]
```

### Supervisor Configuration

```ini
[program:altobul-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/artisan queue:work redis --sleep=3 --tries=3 --timeout=60 --backoff=10,30,60
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/queue.log

[program:altobul-reverb]
command=php /var/www/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/reverb.log
```

### Required Services
- PostgreSQL 16 + PostGIS
- Redis 7
- Laravel Reverb (port 8080)

## Project Structure

```
backend/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Domain/Authorization/    # Value objects for auth
│   ├── Events/Broadcast/        # WebSocket events
│   ├── Exceptions/              # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/         # API controllers
│   │   ├── Middleware/          # API Key, Idempotency, Admin auth
│   │   ├── Requests/            # Form requests
│   │   └── Resources/           # API resources
│   ├── Models/                  # Eloquent models
│   ├── Policies/                # Authorization policies
│   ├── Services/                # Business logic
│   │   ├── Auth/
│   │   ├── Authorization/       # Centralized auth service
│   │   ├── Config/
│   │   ├── Discovery/
│   │   ├── Geo/
│   │   ├── Photo/
│   │   └── Profile/
│   └── Traits/                  # Model traits (UUID, Expiration)
├── bootstrap/
├── config/
│   ├── broadcasting.php         # Reverb + Pusher config
│   ├── queue.php                # Queue connections (database, redis, failover)
│   └── reverb.php               # Reverb server config
├── database/
│   ├── factories/
│   ├── migrations/              # PostGIS-enabled migrations
│   └── seeders/
├── routes/
│   ├── api.php                  # Client + Admin API routes
│   ├── channels.php             # Broadcast channel auth
│   └── console.php
├── tests/
│   ├── Feature/                 # Integration tests
│   └── Unit/                    # Unit tests
├── .github/workflows/ci.yml     # GitHub Actions CI
├── composer.json
├── phpstan.neon                 # PHPStan config (Larastan level 5)
├── phpunit.xml
└── artisan
```

## License

MIT