# Progress

## Current Goal
Improve the Altobul project in small safe steps.

## Agent Rules
- Do not ask questions unless truly blocked.
- Make reasonable assumptions and continue.
- Work on unfinished TODOs in order.
- Mark completed TODOs with [x].
- Add new bugs, ideas, or follow-up tasks as TODOs.
- Run tests/lint/build when available.
- Do not run destructive commands, force pushes, production deploys, or database resets.

## Active TODO
- (none - all achievable tasks completed)

## Completed
- [x] Initial project setup
- [x] Review project structure
- [x] Fix admin/.gitignore to exclude Laravel runtime artifacts (bootstrap/cache/*.php)
- [x] Remove already-tracked cache/compiled artifacts from git index
- [x] Create Laravel-standard .gitignore files inside storage subdirectories
- [x] Backend Pint (code style) PASSING
- [x] Admin Pint: auto-fixed 3 files, now PASSING
- [x] Admin composer install: unblocked by fixing bootstrap/cache permissions via Docker
- [x] Backend tests: improved from 9→99 passing (99/100, 1 skipped)
- [x] Created root .gitignore for project-level artifacts
- [x] Fixed docker-compose.yml: added 5433 port mapping, switched to postgis image, removed deprecated version attribute
- [x] Started PostGIS postgres container on port 5433 (test port)
- [x] Fixed bcrypt→argon2id mismatch in test helper methods (3 test files)
- [x] Added missing config route (ConfigController was registered but route missing)
- [x] Fixed social domain tests: added BROADCAST_CONNECTION=null to phpunit.xml.dist (Reverb not configured in test env)
- [x] Unskipped test_relationship_status_can_chat_method (PHP 8 match conflict no longer exists)
- [x] Backend tests: 100/100 PASSING
- [x] Removed noisy Log::debug calls from ApiKeyMiddleware and AdminAuthorizationMiddleware
- [x] Pint auto-fixed UserController and UserResource style
- [x] Fixed ConfigController route: renamed show()→index() to match route registration
- [x] Removed unused imports (PhotoResource, PostResource) from UserController
- [x] Removed redundant try-catch in PhotoController::store()
- [x] Added AdminUserController import alias, replaced FQCNs in routes/api.php
- [x] Registered SystemController routes (health + compatibility endpoints)
- [x] Pint: fixed ContentModerationController and routes/api.php style
- [x] Removed dead code: FriendshipRequestController::reject() (no route, unreachable)
- [x] Added missing FriendshipRequestController::store() method (route existed but method missing - real bug)
- [x] Removed unused imports: ProfileController (User), DiscoveryService (Profile)
- [x] Removed dead code: UserController::photos(), posts() (no routes, referenced non-imported classes)
- [x] Removed dead code: AuthController::listVerificationRequests(), reviewVerificationRequest() (duplicate of Admin controllers)

## Backlog Ideas
- [ ] Check backend/.gitignore for missing entries (looks fine - has bootstrap/cache/.gitignore)
- [ ] Run PHPStan (blocked by system PHP 8.4 deprecation notices)
- [ ] Write admin panel tests (no test infrastructure currently exists)

## Blocked
- PHPStan: system PHP 8.4 Composer deprecation notices cause exit code 1. Not a project code issue.
