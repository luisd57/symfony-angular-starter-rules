# Scaffolding Guide

Agent instructions for bootstrapping a new project from this kit. Follow in order. Ask before deviating.

## 0. Inputs (ask the user if missing)

- Project name (used for container prefix, network name, DB name, JWT cookie name)
- Which apps: `API/` (always), `app/` (Angular — authenticated web UI), `landing/` (Astro + Svelte — public site). Skip an app = delete its rules file from `.claude/rules/`.
- Product-Requirements.md or Product-brainstorm file, if available. Read it before scaffolding — it drives domain names, roles, and entities. If absent, scaffold structure only and leave domain placeholders.

## 1. Repo Root

1. Copy `CLAUDE.md` (template) and `.claude/` from this kit into the project root. Fill every `{{...}}` token and `{{FILL: ...}}` section; delete the template comment block.
2. Create `docs/` for plans/specs, and a root `README.md` (stack badges, feature list, architecture diagram — see documentation-style rule).
3. Create `docker-compose.yml` with services (prefix container names with the project name, one bridge network):
   - `php` — build from `API/docker/php/Dockerfile`; volumes: `./API:/var/www/html`, named volumes for `vendor/` and `var/`. If the image runs as a non-root user, the Dockerfile MUST `mkdir -p` every named-volume path before `chown`: Docker initializes a volume whose path is absent from the image as `root:root`, and `composer install` then fails with "vendor/... does not exist and could not be created".
   - `nginx` — nginx:alpine, port 8080→80, conf from `API/docker/nginx/default.conf`
   - `postgres` — postgres:16-alpine (or current), port bound to `127.0.0.1:5432`, named data volume
   - `redis` — redis:7-alpine (or current) with `--requirepass`, port bound to `127.0.0.1:6379`
   - `mailhog` — ports 1025/8025
   - `pgadmin` — port 5050, preconfigured `servers.json` + pgpassfile from `API/docker/pgadmin/`
   - `cron` — only if the domain needs scheduled commands; own Dockerfile in `API/docker/cron/`
   - `app` — node:22-alpine (or current LTS), volume-mounted, `ng serve --host 0.0.0.0 --poll 2000`, port 4200
   - `landing` — node:22-alpine, volume-mounted, `npm run dev -- --host 0.0.0.0`, port 4321
   - `playwright` (+ `playwright-report`) — one-shot e2e runners under an `e2e` profile, image pinned to the same minor as `@playwright/test`
4. Create root `Makefile` wrapping docker-compose: `build up down restart logs shell`, `composer c=...`, `sf c=...`, `db-create db-migrate db-diff cache-clear`, `test test-unit test-integration test-db-setup`.
5. `docker-compose.ci.yml` override for CI comes later, with CI setup.

## 2. API (Symfony, hexagonal)

1. Init Symfony (current stable, minimal skeleton) inside the php container.
2. Require: `doctrine/orm` + migrations, `lexik/jwt-authentication-bundle`, validator, mailer, `predis/predis` (or ext-redis), phpunit + `symfony/test-pack`.
3. Create the layer skeleton per `api-architecture.md`:
   - `src/Domain/`, `src/Application/`, `src/Infrastructure/` with per-subdomain folders derived from the requirements doc (e.g. `Security/`, plus one folder per business domain)
4. Cross-cutting base classes FIRST: copy from this kit's `boilerplate/API/` (see `boilerplate/README.md` — some files are verbatim, some have `{{...}}` tokens or `{{FILL}}` sections). Do NOT reimplement them from scratch. Then add the CLI command `app:create-{{admin_role}}` for privileged account creation.
5. Test infra: copy `boilerplate/API/tests/` per its README, then extend `ApiTestCase`/`DomainTestHelper` for this project's roles and entities. phpunit suites `Unit` and `Integration`; separate test DB via `--env=test`.
6. First vertical slice: User entity + VOs (UserId, Email, Role) + login/logout/me endpoints, fully tested. This validates every layer before domain work starts.

## 3. Angular app (`app/`)

1. `ng new` (current stable): standalone, no Zone.js, SCSS, routing.
2. Structure per `angular.md`: domain folders with `feature/ ui/ data-access/ utils/`, plus `shared/`.
3. Set up: `app.config.ts` functional providers, HTTP interceptor (withCredentials + 401→logout ONLY — envelope unwrapping is per-service `unwrap<T>()`), `proxy.conf.json` → nginx :8080 AND wire it up in `angular.json` under `serve.options.proxyConfig` (creating the file alone does nothing, and no test catches it because Vitest mocks HTTP — verify by loading a page in the dev server), auth guard + role-based routes, Vitest, ESLint (typedef + strictTypeChecked, per `angular.md`).
4. Playwright e2e in `app/e2e/`, run via the compose `e2e` profile.

## 4. Landing (`landing/`)

1. `npm create astro@latest` + Tailwind; add Svelte integration only when an interactive island is needed.
2. Structure per `astro-landing.md`. API base URL from `PUBLIC_API_BASE_URL` env.
3. Playwright e2e self-contained (own webServer), API reached at `http://nginx/api` inside the compose network.

## 5. CI (GitHub Actions)

- `test` job (required): API PHPUnit + app lint/build + landing build
- `e2e` job (advisory): Playwright via docker-compose + `docker-compose.ci.yml`
- Protect `main`, require PRs, `test` check must pass.

## 6. Verify

- `make up` → all containers healthy; API responds at `http://localhost:8080/api`; app at :4200; landing at :4321; MailHog at :8025.
- `make test` green; one e2e smoke test green via the `e2e` profile.
- Update CLAUDE.md Implementation Status; delete unused rules files and this file's unused sections.
