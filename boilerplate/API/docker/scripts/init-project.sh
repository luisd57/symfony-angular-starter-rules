#!/bin/bash
# Bootstraps a fresh Symfony API inside the php container. Run once, via `make init`.
set -euo pipefail

echo "=== Creating the Symfony skeleton ==="
# Pin to the major the kit targets; drop the constraint to take current stable.
composer create-project symfony/skeleton:"8.0.*" temp_project --no-interaction
cp -r temp_project/. .
rm -rf temp_project

echo "=== Installing packages ==="
composer require --no-interaction \
    symfony/orm-pack \
    symfony/security-bundle \
    symfony/validator \
    symfony/serializer \
    symfony/property-access \
    symfony/mailer \
    symfony/uid \
    symfony/rate-limiter \
    lexik/jwt-authentication-bundle \
    nelmio/cors-bundle \
    predis/predis

composer require --dev --no-interaction \
    symfony/maker-bundle \
    symfony/debug-bundle \
    symfony/profiler-pack \
    symfony/test-pack \
    phpunit/phpunit

echo "=== Generating the JWT keypair ==="
# The passphrase must match JWT_PASSPHRASE in .env, or the bundle cannot read the
# key at runtime. Nothing else generates these — skip this and auth never boots.
: "${JWT_PASSPHRASE:?set JWT_PASSPHRASE in API/.env before running init}"

if php bin/console list lexik:jwt >/dev/null 2>&1; then
    php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
else
    # Fallback if the bundle's command is unavailable.
    mkdir -p config/jwt
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa \
        -pkeyopt rsa_keygen_bits:4096 -pass pass:"$JWT_PASSPHRASE"
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout \
        -passin pass:"$JWT_PASSPHRASE"
fi

echo "=== Done. Next: copy the boilerplate config, then make db-create db-migrate ==="
