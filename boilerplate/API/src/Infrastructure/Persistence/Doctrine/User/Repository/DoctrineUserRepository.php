<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Id\UserId;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * The repository flushes; handlers never do. Value objects are unwrapped before
 * they reach Doctrine.
 */
final class DoctrineUserRepository implements UserRepositoryInterface
{
    /** @var EntityRepository<User> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $entityManager->getRepository(User::class);
    }

    public function save(User $user): void
    {
        if (!$this->entityManager->contains($user)) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?User
    {
        return $this->entityManager->find(User::class, $id->getValue());
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email->getValue()]);
    }

    public function existsByEmail(Email $email): bool
    {
        return $this->repository->count(['email' => $email->getValue()]) > 0;
    }

    /**
     * @return ArrayCollection<int, User>
     */
    public function findByRole(UserRole $role): ArrayCollection
    {
        return new ArrayCollection($this->repository->findBy(['role' => $role->value]));
    }
}
