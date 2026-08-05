---
name: scaffold-project
description: Bootstrap a new Symfony + Angular + Astro project from this kit
disable-model-invocation: true
---

Ordered guide for bootstrapping a new project from this kit. Follow the phases in order.
Ask before deviating.

## 0. Inputs (ask the user if missing)

- Project name — used for container prefix, network name, DB name, JWT cookie name.
- Which apps: `API/` (always), `app/` (Angular — authenticated web UI), `landing/` (Astro +
  Svelte — public site). Skipping an app means deleting its rules file from `.claude/rules/`.
- A Product-Requirements or brainstorm document, if one exists. Read it before scaffolding —
  it drives domain names, roles, and entities. If absent, scaffold structure only and leave
  domain placeholders.

## 1. Repo root

1. Copy `CLAUDE.md` and `.claude/` from this kit into the project root. Fill every `{{...}}`
   token and `{{FILL: ...}}` section; delete the template comment block.
2. Create `docs/` (with `STATUS.md`) and a root `README.md` — stack badges, feature list,
   architecture diagram.
3. Create `docker-compose.yml`. Service-by-service spec: `references/docker-compose.md`.
4. Create the root `Makefile` wrapping docker compose: `build up down restart logs shell`,
   `composer c=...`, `sf c=...`, `db-create db-migrate db-diff cache-clear`,
   `test test-unit test-integration test-db-setup`.
5. `docker-compose.ci.yml` comes later, with CI setup (phase 5).

## 2. API (Symfony, hexagonal)

1. Init Symfony (current stable, minimal skeleton) inside the php container.
2. Require: `doctrine/orm` + migrations, `lexik/jwt-authentication-bundle`, validator, mailer,
   `predis/predis` (or ext-redis), phpunit + `symfony/test-pack`.
3. Create the layer skeleton per `api-architecture.md`: `src/Domain/`, `src/Application/`,
   `src/Infrastructure/`, with per-subdomain folders derived from the requirements doc
   (e.g. `Security/`, plus one folder per business domain).
4. Cross-cutting base classes FIRST — copy from `boilerplate/API/`, do not reimplement.
   Copy manifest and per-file token list: `references/boilerplate.md`. Then add the CLI
   command `app:create-{{admin_role}}` for privileged account creation.
5. Test infra: copy `boilerplate/API/tests/`, then extend `ApiTestCase` / `DomainTestHelper`
   for this project's roles and entities. phpunit suites `Unit` and `Integration`; separate
   test DB via `--env=test`.
6. First vertical slice: User entity + VOs (UserId, Email, Role) + login/logout/me endpoints,
   fully tested. This validates every layer before domain work starts.

## 3. Angular app (`app/`)

1. `ng new` (current stable): standalone, no Zone.js, SCSS, routing.
2. Structure per `angular.md` — domain folders with `feature/ ui/ data-access/ utils/`, plus
   `shared/`.
3. Set up: functional providers in `app.config.ts`; HTTP interceptor (`withCredentials` +
   401 → logout only — envelope unwrapping is a per-service `unwrap<T>()`); auth guard and
   role-based routes; Vitest; ESLint per `angular.md`.
4. `proxy.conf.json` → nginx :8080, **and** wire it in `angular.json` under
   `serve.options.proxyConfig`. Creating the file alone does nothing, and no test catches the
   omission because Vitest mocks HTTP — verify by loading a page in the dev server.
5. Playwright e2e in `app/e2e/`, run via the compose `e2e` profile.

## 4. Landing (`landing/`)

1. `npm create astro@latest` + Tailwind. Add the Svelte integration only when an interactive
   island is actually needed.
2. Structure per `astro-landing.md`. API base URL from `PUBLIC_API_BASE_URL`.
3. Playwright e2e self-contained (own `webServer`); the API is reached at `http://nginx/api`
   inside the compose network.

## 5. CI (GitHub Actions)

- `test` job (required): API PHPUnit + app lint/build + landing build.
- `e2e` job (advisory): Playwright via docker compose + `docker-compose.ci.yml`.
- Protect `main`, require PRs, `test` check must pass.

## 6. Verify

- `make up` → all containers healthy. API responds at `http://localhost:8080/api`; app at
  :4200; landing at :4321; MailHog at :8025.
- `make test` green, and one e2e smoke test green via the `e2e` profile.
- Write the initial `docs/STATUS.md`. Delete unused rules files, and delete
  `.claude/skills/scaffold-project/` — it has done its job.
