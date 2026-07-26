# Altobul - Agent Instructions

## Project Status
**Design/specification phase only** - No code implemented yet. See `README.md` for full product specification.

## Tech Stack (Planned)
- **Frontend**: Next.js, React, TypeScript, Tailwind CSS
- **Backend**: Laravel, PHP, API, WebSockets
- **Database**: PostgreSQL + PostGIS (geospatial)
- **Cache/Queue**: Redis
- **Storage**: S3-compatible object storage
- **Maps**: Interactive admin map with PostGIS polygons

## Key Domain Concepts
- **Progressive interaction**: Descubrir → Toke → Match (7 days) → Amistad (permanent)
- **Privacy levels**: Público / Match / Amigos / Privado (+ optional "verified only")
- **Verification**: Optional, 3 states (No verificado / Pendiente / Verificado)
- **Geo-zones**: Admin-defined polygons via PostGIS
- **Content TTL**: Toke 48h, Match 7d, Posts 24h, Amistad permanent

## For Future Development
When code is added, update this file with:
- Exact dev commands (install, build, test, lint, typecheck)
- Package/workspace structure if monorepo
- Test conventions and how to run single tests
- CI/CD and pre-commit hooks
- Any framework quirks or generated code locations