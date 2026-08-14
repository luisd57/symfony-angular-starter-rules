<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\Enum\UserRole;
use App\Domain\User\Id\UserId;
use App\Domain\User\ValueObject\Email;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Never `final` - Doctrine proxies it.
 * Never reads the clock: every factory and mutator takes an explicit $now.
 *
 * {{FILL: add this project's profile fields (phone, address, avatar...) as
 *  properties with their own mutators. Multi-field VOs use #[ORM\Embedded] and
 *  need their ValueObject directory registered in doctrine.yaml.}}
 */
#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['email'], name: 'idx_users_email')]
#[ORM\Index(columns: ['role'], name: 'idx_users_role')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $activatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'user_id')]
        private readonly UserId $id,
        #[ORM\Column(type: 'email', length: 255, unique: true)]
        private readonly Email $email,
        #[ORM\Column(type: Types::STRING, length: 255)]
        private readonly string $fullName,
        #[ORM\Column(type: Types::STRING, length: 50, enumType: UserRole::class)]
        private readonly UserRole $role,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private readonly DateTimeImmutable $createdAt,
    ) {
        $this->updatedAt = $createdAt;
    }

    // --- Symfony Security ---

    public function getUserIdentifier(): string
    {
        return $this->email->getValue();
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->role->getSecurityRoles();
    }

    public function eraseCredentials(): void
    {
    }

    // --- Factories ---

    /**
     * Active immediately, with a password. Used by the create-admin CLI command.
     */
    public static function createActive(
        UserId $id,
        Email $email,
        string $fullName,
        UserRole $role,
        string $hashedPassword,
        DateTimeImmutable $now,
    ): self {
        $user = new self($id, $email, $fullName, $role, $now);
        $user->password = $hashedPassword;
        $user->isActive = true;
        $user->activatedAt = $now;

        return $user;
    }

    /**
     * Created without a password, activated later by whatever onboarding flow
     * the project uses.
     */
    public static function createPending(
        UserId $id,
        Email $email,
        string $fullName,
        UserRole $role,
        DateTimeImmutable $now,
    ): self {
        return new self($id, $email, $fullName, $role, $now);
    }

    // --- Mutators ---

    public function activate(string $hashedPassword, DateTimeImmutable $now): void
    {
        if ($this->isActive) {
            throw new \DomainException('User is already active.');
        }

        $this->password = $hashedPassword;
        $this->isActive = true;
        $this->activatedAt = $now;
        $this->updatedAt = $now;
    }

    public function updatePassword(string $hashedPassword, DateTimeImmutable $now): void
    {
        $this->password = $hashedPassword;
        $this->updatedAt = $now;
    }

    public function deactivate(DateTimeImmutable $now): void
    {
        $this->isActive = false;
        $this->updatedAt = $now;
    }

    // --- Getters ---

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getActivatedAt(): ?DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
