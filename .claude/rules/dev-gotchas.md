# Dev Gotchas

Behaviour that looks like a bug and isn't, plus traps this stack has already hit.
Append project-specific entries; delete any section the project doesn't use.

## Docker / build

- `vendor/` and `var/` are named volumes, so `composer install` never populates the host directory and every Symfony/Doctrine symbol shows as undefined in the editor. Expected, not a broken setup - the container is fine. To silence it, snapshot the volume onto the host (gitignored; redo after any dependency change):
  ```bash
  MSYS_NO_PATHCONV=1 docker compose exec -T php tar -cf - -C //var/www/html vendor > /tmp/vendor.tar
  tar -xf /tmp/vendor.tar -C API/
  ```
  `docker cp` and `docker compose cp` both fail here (`mkdir .../vendor: file exists`) - Docker will not copy out of a volume mount point. In Git Bash, container paths need `MSYS_NO_PATHCONV=1` and a leading `//`.
- `vendor/` and `var/` must exist in the image before the `chown`, or Docker initialises those named volumes root-owned and `composer install` fails with "vendor/symfony does not exist and could not be created". Both Dockerfiles `mkdir -p` them first.
- On a fresh CI runner the named volumes mount empty and root-owned; `chown -R symfony:symfony` them before the first composer run.
- Symfony's `Dotenv::bootEnv` requires `API/.env` to exist even when every value comes from the environment. CI `touch`es it.
- `composer create-project` and flex recipes overwrite `config/services.yaml`. If a composer run follows a config copy, the hand-written bindings are gone and the next container compile fails on `RateLimitSubscriber`'s `$apiLoginLimiter`. Re-check after any recipe runs.
- In Git Bash, `docker compose exec` mangles container paths into Windows ones (`bash: C:/Program Files/Git/var/www/...`). Prefix with `MSYS_NO_PATHCONV=1` and use a leading `//`.
- `config/jwt` is host-owned when bind-mounted; make it writable before the container's non-root user generates the keypair.

## Timezone

Three places must agree, and only two are obvious:
- `date.timezone` in `API/docker/php/php.ini` (server side - easy to forget)
- `TZ` for the Playwright browser, or a UTC container shifts every rendered local timestamp
- Postgres stores `TIMESTAMP WITHOUT TIME ZONE`, i.e. local wall-clock, so any day-bucketing depends on the php.ini value. Change it and the buckets move.

## Migrations

Never keep a `doctrine:migrations:diff` result unread. Entities declare no relation attributes, so Doctrine does not know about the hand-written FK constraints and indexes in `migrations/` and proposes dropping all of them. Write schema migrations by hand.

## Testing

- The test database is separate and persistent. Run `make test-db-setup` once after a fresh clone, and again after `down -v` or any new migration - otherwise integration tests fail in confusing ways.
- `ApiTestCase::freezeClock()` must be called BEFORE the request that resolves the clock-using handler, or the frozen time is ignored.
- Kernel reboot is disabled in API tests so a transaction spans multiple HTTP requests.

## E2E (Playwright)

- Pin the container image to the same minor as `@playwright/test`; a mismatch between the client and the bundled browsers fails at runtime.
- `globalSetup` must wait for the dev server's first compile. The container is up before Angular is serving, so the suite otherwise races the build.
- Authenticate once in `globalSetup` and persist `storageState`. Route guards read `localStorage` synchronously, before `/auth/me` resolves, so a cookie-only state bounces every protected route to `/login`. Logging in once also avoids tripping the API's own login rate limiter, which fires when many tests authenticate from a single container IP.
- Don't gate assertions on `networkidle`: it fires before a lazily-loaded route's chunk issues its XHRs, so a correct page screenshots as empty. Wait on the content itself.
- Serve `playwright-report/` over HTTP (the `playwright-report` service). The trace viewer runs on a Service Worker and shows nothing over `file://`.
- The landing e2e server binds `127.0.0.1` so the page origin satisfies the API's loopback CORS rule, and overrides `PUBLIC_API_BASE_URL` to `http://nginx/api` - the `.env` default is unreachable from inside the container.
