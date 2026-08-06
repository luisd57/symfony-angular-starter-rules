<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Id\UserId;
use App\Domain\User\ValueObject\Email;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * {{FILL: add the project's own finders. Keep returning ArrayCollection for
 *  multi-result methods, and unwrap value objects in the implementation.}}
 */
interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function delete(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;

    /**
     * @return ArrayCollection<int, User>
     */
    public function findByRole(UserRole $role): ArrayCollection;
}
