<?php

declare(strict_types=1);

namespace App\Application\User\DTO\Output;

/**
 * Internal carrier from handler to controller - deliberately has no toArray().
 * The token goes into an httpOnly cookie and only the user reaches the response
 * body; a toArray() here would imply the whole thing is safe to serialize,
 * which is exactly the leak the cookie design prevents.
 */
final readonly class AuthResultOutputDTO
{
    public function __construct(
        public string $token,
        public UserOutputDTO $user,
    ) {
    }
}
