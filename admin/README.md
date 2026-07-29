# Altobul Admin Panel

Panel de administración para la plataforma Altobul, desarrollado con Laravel 12.

## Requisitos

- PHP 8.3+
- Composer
- Node.js 18+ y npm
- Backend API Altobul corriendo (Laravel 12)

## Instalación

```bash
# Clonar e instalar dependencias
cd admin
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos (SQLite por defecto)
touch database/database.sqlite
php artisan migrate

# Compilar assets
npm run build
```

## Configuración

Edita `.env` con las credenciales del backend:

```env
ADMIN_API_BASE_URL=https://api.altobul.com
ADMIN_API_KEY=ak_admin_xxxxxxxxxxxxx
```

La API Key de tipo ADMIN se genera desde el propio backend:
```bash
php artisan api-keys:create --type=ADMIN --name="Admin Panel"
```

## Desarrollo

```bash
# Servidor de desarrollo con hot reload
npm run dev

# En otra terminal
php artisan serve --port=8001
```

## Estructura

```
admin/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Controladores del panel admin
│   │   │   └── Auth/           # Autenticación web
│   │   ├── Middleware/         # Middlewares personalizados
│   │   └── Requests/           # Validaciones FormRequest
│   ├── Services/
│   │   └── BackendApiService.php  # Cliente HTTP para API backend
│   └── View/Components/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
│   ├── views/
│   │   ├── admin/              # Vistas del panel (Blade)
│   │   └── auth/               # Login
│   ├── js/
│   └── css/
├── routes/
│   └── web.php                 # Rutas web
├── storage/
├── tests/
├── artisan
├── composer.json
├── package.json
├── vite.config.mjs
└── .env.example
```

## Módulos

1. **Dashboard** - Métricas y gráficos (Chart.js)
2. **GeoZonas** - CRUD + mapa interactivo (Leaflet + Leaflet.draw)
3. **Usuarios** - Listado, detalle con tabs, suspensión/activación, cambio de rol
4. **Campos de Perfil** - CRUD + drag&drop reorder + gestor de opciones dinámico
5. **Verificaciones** - Cola, detalle, aprobar/rechazar
6. **Configuración** - Tabla key/value con editor JSON
7. **API Keys** - Listar, crear (raw key una vez), revocar
8. **Auditoría** - Logs filtrables con metadata JSON

## Despliegue

Ver `DEPLOY.md` para instrucciones de producción con Nginx + Supervisor.