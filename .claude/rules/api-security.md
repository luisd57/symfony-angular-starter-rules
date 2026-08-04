---
paths:
  - API/src/Infrastructure/**/*.php
  - API/config/**/*.yaml
---
# API Security & Infrastructure

## Authentication
- JWT via `lexik/jwt-authentication-bundle` with `jti` claim for Redis-backed revocation
- Transport: single httpOnly cookie `{{PROJECT}}_JWT` (`Path=/api`, `SameSite=Lax`) for browsers; Bearer token for API clients. One session per browser — login (any role) replaces the cookie. Lexik's built-in cookie extractor reads it; Authorization header is the fallback.
- Cookie managed by `JwtCookieManager` (name in `JwtCookieManager::COOKIE_NAME`). `JWT_COOKIE_SECURE` controls Secure flag. Logout clears the cookie + revokes the token's jti.
- CORS with `allow_credentials: true`, scoped to `^/api/`, origin from `APP_FRONTEND_URL`

## Access Control
- Admin/privileged account creation: CLI only (`app:create-{{admin_role}}`) — no HTTP endpoint
- Privileged endpoints: class-level `#[IsGranted('ROLE_{{ADMIN_ROLE}}')]` on the controller
- Public endpoints: explicitly listed in `security.yaml`; everything else authenticated by default

## Rate Limiting
- Via `RateLimitSubscriber`: login 5/min, public endpoints 10/min
- Must cover: login, forgot-password, registration, and every unauthenticated write endpoint
- Limiter is selected by route NAME (`match($route)`) — renaming a route silently drops its limit

## Token Security
- Any single-use token (invitation, password reset, etc.) stored hashed (SHA-256); raw value exists only at creation time

## Environment Variables
- Core: `DATABASE_URL`, `REDIS_URL`, `JWT_PASSPHRASE`, `APP_FRONTEND_URL`
- JWT: `JWT_TOKEN_TTL` (default: 3600), `JWT_COOKIE_SECURE` (default: false for dev)
- Tokens: one `{{NAME}}_TOKEN_TTL` var per single-use token type
- `TRUSTED_PROXIES` (default: REMOTE_ADDR)
- {{FILL: domain-specific config vars with defaults}}
