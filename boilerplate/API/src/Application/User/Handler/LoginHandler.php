<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\DTO\Input\LoginInputDTO;
use App\Application\User\DTO\Output\AuthResultOutputDTO;
use App\Application\User\DTO\Output\UserOutputDTO;
use App\Domain\User\Exception\InvalidCredentialsException;
use App\Domain\User\Exception\UserNotActiveException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\JwtTokenGeneratorInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\Email;

/**
 * Role-agnostic login. If the project needs per-role entry points, branch in the
 * controller and pass the expected UserRole in - don't duplicate this handler.
 */
final readonly class LoginHandler
{
    /**
     * Verified against when the email is unknown, so the response takes the same
     * time either way and cannot be used to enumerate accounts.
     */
    private const string DUMMY_BCRYPT_HASH = '$2y$12$v2yYa3Zba1sfc7LMzCdWy.Z5ROqo2i7xIeXhSzQVv/Lt5FoYIOxii';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private JwtTokenGeneratorInterface $jwtTokenGenerator,
    ) {
    }

    public function __invoke(LoginInputDTO $dto): AuthResultOutputDTO
    {
        $email = Email::fromString($dto->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            $this->passwordHasher->verify($dto->password, self::DUMMY_BCRYPT_HASH);

            throw new InvalidCredentialsException();
        }

        if (!$user->isActive()) {
            throw new UserNotActiveException();
        }

        $storedPassword = $user->getPassword();

        if ($storedPassword === null || !$this->passwordHasher->verify($dto->password, $storedPassword)) {
            throw new InvalidCredentialsException();
        }

        return new AuthResultOutputDTO(
            token: $this->jwtTokenGenerator->generate($user),
            user: UserOutputDTO::fromEntity($user),
        );
    }
}
