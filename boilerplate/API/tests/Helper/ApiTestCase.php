<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\Id\UserId;
use App\Infrastructure\Security\JwtCookieManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            $this->entityManager->close();
        } finally {
            parent::tearDown();
        }
    }

    protected function jsonRequest(string $method, string $uri, array $data = [], ?string $token = null): void
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($token !== null) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        } else {
            // Clear any lingering auth cookie to ensure truly unauthenticated requests
            $this->client->getCookieJar()->expire(JwtCookieManager::COOKIE_NAME, '/api');
        }
        $this->client->request($method, $uri, [], [], $headers, json_encode($data));
    }

    protected function getResponseData(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    // {{FILL: one create{Role}AndGetToken() helper per user role. Pattern below -
    //  adapt factory name, login route, and default credentials to this project.}}
    protected function createAdminAndGetToken(
        string $email = 'admin@test.com',
        string $password = 'Password1!',
    ): string {
        $hasher = self::getContainer()->get(PasswordHasherInterface::class);
        $repo = self::getContainer()->get(UserRepositoryInterface::class);

        $admin = User::createAdmin(
            id: UserId::generate(),
            email: Email::fromString($email),
            fullName: 'Test Admin',
            hashedPassword: $hasher->hash($password),
            now: new \DateTimeImmutable(),
        );
        $repo->save($admin);

        $this->jsonRequest('POST', '/api/auth/admin/login', [
            'email' => $email,
            'password' => $password,
        ]);

        return $this->extractTokenFromCookie(JwtCookieManager::COOKIE_NAME);
    }

    /**
     * Replace the container's ClockInterface with a frozen MockClock so date-dependent
     * tests can pin "now" to a fixed instant. Must be called before the test triggers
     * the request that resolves the clock-using handlers.
     */
    protected function freezeClock(string $now): MockClock
    {
        $clock = new MockClock(new \DateTimeImmutable($now));
        self::getContainer()->set(ClockInterface::class, $clock);

        return $clock;
    }

    private function extractTokenFromCookie(string $cookieName): string
    {
        $cookie = $this->client->getCookieJar()->get($cookieName, '/api');

        return $cookie !== null ? $cookie->getValue() : '';
    }
}
