---
name: scaffold-project
description: Bootstrap a new Symfony + Angular + Astro project from this kit
disable-model-invocation: true
---

Ordered guide for bootstrapping a new project from this kit. Follow the phases in order.
Ask before deviating.

Most of what you need is a real file in `boilerplate/`, not a description. Copy it and
replace the tokens - the copy manifest is `references/boilerplate.md`, and every token is
listed in `references/tokens.md`.

## 0. Inputs (ask the user if missing)

- Project name - used for container names, database, and the JWT cookie. Gives `{{project}}`
  (lowercase slug), `{{PROJECT}}` (uppercase) and `{{PROJECT_NAME}}` (human readable).
- Timezone `{{TZ}}` the app reasons in.
- Role names: `{{ADMIN_ROLE}}` / `{{USER_ROLE}}` and their route prefixes.
- Which apps: `API/` (always), `app/` (Angular), `landing/` (Astro + Svelte). Skipping one
  means deleting its rules file, its `boilerplate/` directory, and its compose services.
- A Product-Requirements or brainstorm document, if one exists. Read it before scaffolding -
  it drives domain names, roles, and entities. If absent, scaffold structure only.

## 1. Repo root

1. Copy `CLAUDE.md`, `.claude/` and `.mcp.json` into the project root. Fill every `{{...}}`
   token and `{{FILL: ...}}` section; delete the template comment block.
2. Copy `boilerplate/docker-compose.yml`, `docker-compose.ci.yml`, `Makefile` and `.gitignore`
   to the root. Replace `{{project}}` throughout.
3. Create `docs/STATUS.md` and a root `README.md`.
4. Create a root `.env` (gitignored) with `POSTGRES_PASSWORD`, `REDIS_PASSWORD` and
   `PGADMIN_PASSWORD`. Generate each - do not keep a `CHANGE_ME`.

## 2. API (Symfony, hexagonal)

1. Copy the whole of `boilerplate/API/` into `API/`. It is a working Symfony project:
   `composer.json` (pinned), all of `config/`, `src/` with the auth slice, `tests/`,
   `migrations/`, `phpunit.xml.dist`, `.env.example`, `.env.test`, `.gitignore`, `docker/`.
2. Write `API/.env` from `.env.example`, generating real values for `APP_SECRET` and
   `JWT_PASSPHRASE`. Never keep a `CHANGE_ME`.
3. `make build up`, then `make init` - that runs `composer install` and **generates the JWT
   keypair**, which nothing else does and without which auth cannot boot.

   Because `composer.json` is shipped, `composer create-project` never runs and flex never
   regenerates `config/services.yaml`. If you deviate and run create-project anyway, re-check
   `grep apiLoginLimiter config/services.yaml` afterwards.
4. `make db-create db-migrate`, then `make test-db-setup`.
5. Replace the tokens (`references/tokens.md`), then extend rather than rewrite: add the
   project's subdomains under `src/Domain/`, register their Doctrine mappings and VO types in
   `doctrine.yaml`, and bind their repository interfaces in `services.yaml`. Extend
   `ApiTestCase` / `DomainTestHelper` for the new roles and entities.
6. Verify the shipped slice before writing any domain code: `app:create-{{admin_role}}`, then
   `make test`, then log in with `curl` (step 6 below). If that works, every layer and every
   piece of config is proven.

## 3. Angular app (`app/`)

1. Copy the whole of `boilerplate/app/` into `app/`: `package.json`, `angular.json` (already
   wires `serve.options.proxyConfig` - creating `proxy.conf.json` alone does nothing, and no
   test catches the omission because Vitest mocks HTTP), the tsconfigs, `eslint.config.js`,
   `playwright.config.ts`, `e2e/`, and `src/` with the auth slice - `ApiResponse` +
   `unwrap` helpers, interceptor, guard, `AuthService`.
2. `ng new` is only needed for the pieces the kit does not ship (`main.ts`, `index.html`,
   `styles.scss`, `app.routes.ts`, the login page). Generate them into place rather than
   scaffolding over the copied config.
3. Fill the `{{FILL}}` markers in `auth.service.ts` (post-login route) and
   `e2e/global-setup.ts` (login form selectors, post-login URL).
4. Structure per `angular.md`; API conventions per `angular-apis.md`.

## 4. Landing (`landing/`)

1. Copy `boilerplate/landing/` - `package.json`, `astro.config.mjs`, `tailwind.config.mjs`,
   `tsconfig.json`, `playwright.config.ts`, `e2e/`, `src/{services,types}`, `.env.example`.
2. `npm create astro@latest` for the pages/layouts the kit does not ship. Add the Svelte
   integration only when an interactive island is actually needed.
3. Structure per `astro-landing.md`.

## 5. CI

Copy `boilerplate/.github/workflows/ci.yml`. Fill the `{{FILL}}` env vars and the seed step.
Protect `main`, require PRs, make the `test` check required and `e2e` advisory.

## 6. Verify

- `make up` → all containers healthy. API at `http://localhost:8080/api`, app at :4200,
  landing at :4321, MailHog at :8025.
- `make test` green.
- Log in via `curl` and confirm the `{{PROJECT}}_JWT` cookie comes back - that is what proves
  `services.yaml`, `rate_limiter.yaml`, the lexik config and both JWT listeners are wired.
- One e2e smoke test green via the `e2e` profile.
- Write `docs/STATUS.md`. Delete unused rules files, unused `boilerplate/` directories, and
  `.claude/skills/scaffold-project/` - it has done its job.
