# API Boilerplate

Battle-tested infrastructure + test files, extracted from a production project. Copy into the new API during scaffolding (phase 2 of the `/scaffold-project` skill) instead of reimplementing from spec.

## Symfony config — copy, then fill

`API/config/` is not optional scaffolding: the shipped PHP does not work without it.

- `services.yaml` — **required**. Binds `RateLimitSubscriber`'s two `RateLimiterFactory` args (Symfony cannot autowire them) and tags `JwtCreatedListener` / `JwtDecodedListener`. Without it the container fails to compile, and without the tags the listeners are constructed but never called. `{{FILL}}` the per-domain port→adapter entries.
- `packages/rate_limiter.yaml` — **required** by the above; declares `limiter.api_login` / `limiter.api_public`. Keep the `when@dev` relaxation: e2e drives every login from one container IP and trips production limits mid-run.
- `packages/security.yaml` — firewall ORDER matters; the two single-route firewalls must precede the broad public one. Replace `{{ADMIN_ROLE}}` / `{{USER_ROLE}}` and their paths.
- `packages/lexik_jwt_authentication.yaml` — replace `{{PROJECT}}_JWT`, matching `JwtCookieManager.php`.
- `packages/doctrine.yaml` — `{{FILL}}` the VO type list and one mapping pair per subdomain. A multi-field VO needs its `ValueObject/` directory registered or Doctrine never sees it.
- `packages/framework.yaml`, `packages/nelmio_cors.yaml`, `routes.yaml` — copy verbatim.
- `phpunit.xml.dist` — the file `tests/bootstrap.php` is referenced from.
- `.env.example`, `.env.test`, `.gitignore` — every credential is `CHANGE_ME`. Generate `APP_SECRET` and `JWT_PASSPHRASE` per project and never reuse a value from another repo.

## Copy verbatim
- `src/Infrastructure/Http/Controller/ApiResponseTrait.php` — response envelope + pagination
- `src/Infrastructure/Http/Controller/ValidatesRequestTrait.php` — raw-array validation → 422 field map (no `#[Assert]` on DTOs — see api-conventions.md)
- `src/Application/Shared/DTO/PaginatedResultDTO.php` — paginated output wrapper
- `src/Infrastructure/Http/EventSubscriber/SecurityHeadersSubscriber.php`
- `src/Infrastructure/Security/` — BcryptPasswordHasher, JwtTokenGenerator, JwtCreatedListener, JwtDecodedListener, SecureTokenGenerator
- `tests/Helper/IntegrationTestCase.php`, `tests/bootstrap.php`

## Copy, then replace tokens
- `JwtCookieManager.php` — `{{PROJECT}}_JWT` cookie name
- `RedisJwtBlocklist.php` — `{{project}}_jwt_revoked_` key prefix
- `RateLimitSubscriber.php` — `{{FILL}}` route list: every login route + every unauthenticated write route

## Copy, then extend per domain
- `tests/Helper/ApiTestCase.php` — generic base is final; add one `create{Role}AndGetToken()` per role (pattern included)
- `tests/Helper/DomainTestHelper.php` — skeleton; add one factory per entity/state your tests need (created + reconstituted patterns included)

Interfaces these files implement (`PasswordHasherInterface`, `JwtTokenGeneratorInterface`, repository interfaces) belong in `src/Domain/` — create them as driven ports per `api-architecture.md`. Namespaces assume `App\`; adjust if different.
