<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\Id\UserId;
use App\Domain\User\Enum\UserRole;
use DateTimeImmutable;

/**
 * Factory methods for domain objects in controlled states.
 * Tests use these instead of calling constructors directly.
 *
 * {{FILL: one create{Entity}() factory per entity/state combination this project's
 *  tests need. Two patterns below - "created" (via the entity's own factory) and
 *  "reconstituted" (bypasses invariants to reach states only time can produce,
 *  e.g. expired tokens). reconstitute() is for test helpers ONLY.}}
 */
final class DomainTestHelper
{
    public static function createAdmin(
        ?UserId $id = null,
        string $email = 'admin@example.com',
        string $fullName = 'Test Admin',
        string $hashedPassword = 'hashed_password_123',
    ): User {
        return User::createAdmin(
            id: $id ?? UserId::generate(),
            email: Email::fromString($email),
            fullName: $fullName,
            hashedPassword: $hashedPassword,
            now: new DateTimeImmutable(),
        );
    }

    public static function createReconstitutedAdmin(
        ?UserId $id = null,
        string $email = 'admin@example.com',
        string $fullName = 'Test Admin',
        string $hashedPassword = 'hashed_password_123',
    ): User {
        $now = new DateTimeImmutable();

        return User::reconstitute(
            id: $id ?? UserId::generate(),
            email: Email::fromString($email),
            fullName: $fullName,
            role: UserRole::ADMIN,
            password: $hashedPassword,
            isActive: true,
            createdAt: $now,
            activatedAt: $now,
            updatedAt: $now,
        );
    }
}
