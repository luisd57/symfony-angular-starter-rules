# Tokens

Every `{{...}}` appearing in `boilerplate/`. Replace all of them; a leftover token is a
build failure, not a cosmetic issue.

| Token | Meaning | Example |
|---|---|---|
| `{{project}}` | lowercase slug — container names, database, DB user | `bookshelf` |
| `{{PROJECT}}` | uppercase — JWT cookie name (`{{PROJECT}}_JWT`) | `BOOKSHELF` |
| `{{PROJECT_NAME}}` | human readable — pgAdmin server label, CLAUDE.md heading | `Bookshelf` |
| `{{TZ}}` | PHP `date.timezone`; must match how the app reasons about wall-clock time | `Europe/Paris` |
| `{{ADMIN_ROLE}}` / `{{admin_role}}` | privileged Symfony role / its lowercase form for the CLI command | `ROLE_LIBRARIAN` / `librarian` |
| `{{USER_ROLE}}` | regular authenticated role | `ROLE_MEMBER` |
| `{{admin_role_path}}` / `{{user_role_path}}` | route prefixes those roles guard in `security.yaml` | `librarian` / `member` |

`{{FILL: ...}}` (referred to as `{{FILL}}` in prose) marks a section the domain must supply
rather than a find-and-replace. Leaving one in place is fine only if the section genuinely
does not apply — delete it then.

## Where each appears

- `{{project}}` — `docker-compose.yml`, `docker-compose.ci.yml`, `.env.example`, `.env.test`,
  `app/proxy.conf.json`, `API/docker/pgadmin/*`, `.github/workflows/ci.yml`
- `{{PROJECT}}` — `API/config/packages/lexik_jwt_authentication.yaml`, and it must match
  `JwtCookieManager.php`
- `{{TZ}}` — `API/docker/php/php.ini`
- roles — `API/config/packages/security.yaml`, the `app:create-{{admin_role}}` command,
  the CI seed step
