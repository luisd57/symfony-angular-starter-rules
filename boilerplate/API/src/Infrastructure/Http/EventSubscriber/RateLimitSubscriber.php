<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RateLimiterFactory $apiLoginLimiter,
        private readonly RateLimiterFactory $apiPublicLimiter,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();
        $route = $request->attributes->get('_route', '');
        $clientIp = $request->getClientIp() ?? 'unknown';

        $limiter = $this->resolveLimiter($route, $clientIp);
        if ($limiter === null) {
            return;
        }

        $limit = $limiter->consume();
        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter();

            $requestEvent->setResponse(new JsonResponse([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Too many requests. Please try again later.',
                ],
            ], 429, [
                'Retry-After' => $retryAfter->getTimestamp() - time(),
                'X-RateLimit-Limit' => $limit->getLimit(),
                'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
            ]));
        }
    }

    private function resolveLimiter(string $route, string $clientIp): ?\Symfony\Component\RateLimiter\LimiterInterface
    {
        // Matched by route NAME, so renaming a route silently drops its limit.
        // These names must stay in step with the #[Route(name: ...)] attributes.
        //
        // {{FILL: add every additional login route and every unauthenticated
        //  write route this project exposes.}}
        return match ($route) {
            'api_auth_login' => $this->apiLoginLimiter->create($clientIp),
            'api_auth_forgot_password',
            'api_auth_register',
            'api_auth_reset_password' => $this->apiPublicLimiter->create($clientIp),
            default => null,
        };
    }
}
