# Boilerplate copy manifest

Files in `boilerplate/API/` are battle-tested infrastructure extracted from a production
project. Copy them; do not reimplement from spec.

## Copy verbatim

- `src/Infrastructure/Http/Controller/ApiResponseTrait.php` — response envelope + pagination
- `src/Infrastructure/Http/Controller/ValidatesRequestTrait.php` — raw-array validation → 422
  field map (no `#[Assert]` on DTOs — see `api-conventions.md`)
- `src/Application/Shared/DTO/PaginatedResultDTO.php` — paginated output wrapper
- `src/Infrastructure/Http/EventSubscriber/SecurityHeadersSubscriber.php`
- `src/Infrastructure/Security/` — `BcryptPasswordHasher`, `JwtTokenGenerator`,
  `JwtCreatedListener`, `JwtDecodedListener`, `SecureTokenGenerator`
- `tests/Helper/IntegrationTestCase.php`, `tests/bootstrap.php`

## Copy, then replace tokens

- `JwtCookieManager.php` — `{{PROJECT}}_JWT` cookie name
- `RedisJwtBlocklist.php` — `{{project}}_jwt_revoked_` key prefix
- `RateLimitSubscriber.php` — `{{FILL}}` route list: every login route plus every
  unauthenticated write route

## Copy, then extend per domain

- `tests/Helper/ApiTestCase.php` — the generic base is final; add one
  `create{Role}AndGetToken()` per role (pattern included)
- `tests/Helper/DomainTestHelper.php` — skeleton; add one factory per entity/state your tests
  need (created + reconstituted patterns included)

## Notes

The interfaces these files implement (`PasswordHasherInterface`, `JwtTokenGeneratorInterface`,
repository interfaces) belong in `src/Domain/` as driven ports — create them per
`api-architecture.md`. Namespaces assume `App\`; adjust if different.
