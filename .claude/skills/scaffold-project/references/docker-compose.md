# docker-compose.yml services

Prefix container names with the project name. One bridge network.

- **`php`** — build from `API/docker/php/Dockerfile`. Volumes: `./API:/var/www/html`, plus
  named volumes for `vendor/` and `var/`.
  If the image runs as a non-root user, the Dockerfile MUST `mkdir -p` every named-volume path
  before the `chown`. Docker initializes a volume whose path is absent from the image as
  `root:root`, and `composer install` then fails with
  "vendor/... does not exist and could not be created".
- **`nginx`** — `nginx:alpine`, port 8080→80, conf from `API/docker/nginx/default.conf`.
- **`postgres`** — `postgres:16-alpine` (or current), port bound to `127.0.0.1:5432`, named
  data volume.
- **`redis`** — `redis:7-alpine` (or current) with `--requirepass`, port bound to
  `127.0.0.1:6379`.
- **`mailhog`** — ports 1025 / 8025.
- **`pgadmin`** — port 5050, preconfigured `servers.json` + pgpassfile from
  `API/docker/pgadmin/`.
- **`cron`** — only if the domain needs scheduled commands. Own Dockerfile in
  `API/docker/cron/`.
- **`app`** — `node:22-alpine` (or current LTS), volume-mounted,
  `ng serve --host 0.0.0.0 --poll 2000`, port 4200.
- **`landing`** — `node:22-alpine`, volume-mounted, `npm run dev -- --host 0.0.0.0`, port 4321.
- **`playwright`** (+ `playwright-report`) — one-shot e2e runners under an `e2e` profile.
  Pin the image to the same minor as `@playwright/test`.

Version pins above are starting points — check for current stable at scaffold time.
