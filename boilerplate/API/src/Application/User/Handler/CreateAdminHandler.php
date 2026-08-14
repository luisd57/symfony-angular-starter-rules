<?php

declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\DTO\Input\CreateAdminInputDTO;
use App\Application\User\DTO\Output\UserOutputDTO;
use App\Domain\User\Entity\User;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Id\UserId;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\Email;
use Symfony\Component\Clock\ClockInterface;

/**
 * Privileged account creation. Reached only from the CLI - there is deliberately
 * no HTTP endpoint for it.
 */
final readonly class CreateAdminHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private ClockInterface $clock,
    ) {
    }

    public function __invoke(CreateAdminInputDTO $dto): UserOutputDTO
    {
        $email = Email::fromString($dto->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new UserAlreadyExistsException();
        }

        $user = User::createActive(
            id: UserId::generate(),
            email: $email,
            fullName: $dto->fullName,
            role: UserRole::ADMIN,
            hashedPassword: $this->passwordHasher->hash($dto->password),
            now: $this->clock->now(),
        );

        $this->userRepository->save($user);

        return UserOutputDTO::fromEntity($user);
    }
}
