---
name: scaffold-project
description: Bootstrap a new Symfony + Angular + Astro project from this kit
disable-model-invocation: true
---

Ordered guide for bootstrapping a new project from this kit. Follow the phases in order.
Ask before deviating.

Most of what you need is a real file in `boilerplate/`, not a description. Copy it and
replace the tokens — the copy manifest is `references/boilerplate.md`, and every token is
listed in `references/tokens.md`.

## 0. Inputs (ask the user if missing)

- Project name — used for container names, database, and the JWT cookie. Gives `{{project}}`
  (lowercase slug), `{{PROJECT}}` (uppercase) and `{{PROJECT_NAME}}` (human readable).
- Timezone `{{TZ}}` the app reasons in.
- Role names: `{{ADMIN_ROLE}}` / `{{USER_ROLE}}` and their route prefixes.
- Which apps: `API/` (always), `app/` (Angular), `landing/` (Astro + Svelte). Skipping one
  means deleting its rules file, its `boilerplate/` directory, and its compose services.
- A Product-Requirements or brainstorm document, if one exists. Read it before scaffolding —
  it drives domain names, roles, and entities. If absent, scaffold structure only.

## 1. Repo root

1. Copy `CLAUDE.md`, `.claude/` and `.mcp.json` into the project root. Fill every `{{...}}`
   token and `{{FILL: ...}}` section; delete the template comment block.
2. Copy `boilerplate/docker-compose.yml`, `docker-compose.ci.yml`, `Makefile` and `.gitignore`
   to the root. Replace `{{project}}` throughout.
3. Create `docs/STATUS.md` and a root `README.md`.
4. Create a root `.env` (gitignored) with `POSTGRES_PASSWORD`, `REDIS_PASSWORD` and
   `PGADMIN_PASSWORD`. Generate each — do not keep a `CHANGE_ME`.

## 2. API (Symfony, hexagonal)

1. Copy `boilerplate/API/docker/` and run `make build up`, then `make init`. That script
   creates the Symfony skeleton, installs the packages, and **generates the JWT keypair** —
   nothing else does, and auth cannot boot without it. It reads `JWT_PASSPHRASE`, so write
   `API/.env` first (from `boilerplate/API/.env.example`) with generated values for
   `APP_SECRET` and `JWT_PASSPHRASE`.
2. Copy `boilerplate/API/config/`, `phpunit.xml.dist`, `.env.test` and `.gitignore` over the
   generated ones. `config/services.yaml` and `config/packages/rate_limiter.yaml` are what
   make the shipped boilerplate work — see `references/boilerplate.md`.
3. Create the layer skeleton per `api-architecture.md`: `src/Domain/`, `src/Application/`,
   `src/Infrastructure/`, with per-subdomain folders from the requirements doc.
4. Copy `boilerplate/API/src/` and `boilerplate/API/tests/`. Do not reimplement them.
   Extend `ApiTestCase` / `DomainTestHelper` for this project's roles and entities.
5. Add the CLI command `app:create-{{admin_role}}` for privileged account creation.
6. First vertical slice: User entity + VOs (UserId, Email, Role) + login/logout/me, fully
   tested. This exercises every layer and every piece of config above before domain work
   starts. `make db-create db-migrate`, then `make test-db-setup`, then `make test`.

## 3. Angular app (`app/`)

1. `ng new` (current stable): standalone, no Zone.js, SCSS, routing.
2. Copy `boilerplate/app/` over the generated config: `angular.json`, `proxy.conf.json`,
   `tsconfig*.json`, `eslint.config.js`, `.prettierrc`, `.editorconfig`,
   `playwright.config.ts`. The `angular.json` already wires `serve.options.proxyConfig` —
   creating `proxy.conf.json` alone does nothing, and no test catches the omission because
   Vitest mocks HTTP.
3. Structure per `angular.md`; API conventions per `angular-apis.md`.
4. Write `e2e/global-setup.ts` per the notes in `playwright.config.ts`.

## 4. Landing (`landing/`)

1. `npm create astro@latest` + Tailwind. Add the Svelte integration only when an interactive
   island is actually needed.
2. Copy `boilerplate/landing/`. Structure per `astro-landing.md`.

## 5. CI

Copy `boilerplate/.github/workflows/ci.yml`. Fill the `{{FILL}}` env vars and the seed step.
Protect `main`, require PRs, make the `test` check required and `e2e` advisory.

## 6. Verify

- `make up` → all containers healthy. API at `http://localhost:8080/api`, app at :4200,
  landing at :4321, MailHog at :8025.
- `make test` green.
- Log in via `curl` and confirm the `{{PROJECT}}_JWT` cookie comes back — that is what proves
  `services.yaml`, `rate_limiter.yaml`, the lexik config and both JWT listeners are wired.
- One e2e smoke test green via the `e2e` profile.
- Write `docs/STATUS.md`. Delete unused rules files, unused `boilerplate/` directories, and
  `.claude/skills/scaffold-project/` — it has done its job.
