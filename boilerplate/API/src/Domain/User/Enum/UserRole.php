<?php

declare(strict_types=1);

namespace App\Domain\User\Enum;

/**
 * {{FILL: rename the cases to this project's roles. Two is the starting shape -
 *  a privileged role and a regular one - but any number works.}}
 */
enum UserRole: string
{
    case ADMIN = '{{ADMIN_ROLE}}';
    case USER = '{{USER_ROLE}}';

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }

    /**
     * Every role also carries ROLE_USER so `IS_AUTHENTICATED_FULLY` rules in
     * security.yaml behave as expected.
     *
     * @return array<string>
     */
    public function getSecurityRoles(): array
    {
        return match ($this) {
            self::ADMIN => ['{{ADMIN_ROLE}}', 'ROLE_USER'],
            self::USER => ['{{USER_ROLE}}', 'ROLE_USER'],
        };
    }
}
