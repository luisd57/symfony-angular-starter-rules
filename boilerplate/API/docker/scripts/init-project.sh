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
# Nothing else creates these - skip this and auth never boots.
# The console command reads JWT_PASSPHRASE from .env via Symfony's Dotenv, so it
# does NOT need to be exported into the shell. Only the openssl fallback does,
# which is why the passphrase is read out of .env there rather than assumed.
if php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction; then
    :
else
    echo "lexik command unavailable - falling back to openssl" >&2
    PASSPHRASE=$(grep -E '^JWT_PASSPHRASE=' .env | head -1 | cut -d= -f2- | tr -d '"'"'"'"')
    : "${PASSPHRASE:?JWT_PASSPHRASE not found in API/.env}"
    mkdir -p config/jwt
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa \
        -pkeyopt rsa_keygen_bits:4096 -pass pass:"$PASSPHRASE"
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout \
        -passin pass:"$PASSPHRASE"
fi

echo "=== Done. Next: copy the boilerplate config, then make db-create db-migrate ==="
