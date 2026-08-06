<?php

declare(strict_types=1);

namespace App\Application\User\DTO\Input;

final readonly class LoginInputDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
