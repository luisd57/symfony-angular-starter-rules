<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller\Api\Auth;

use App\Application\User\DTO\Input\LoginInputDTO;
use App\Application\User\DTO\Output\UserOutputDTO;
use App\Application\User\Handler\LoginHandler;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\InvalidCredentialsException;
use App\Domain\User\Exception\UserNotActiveException;
use App\Domain\User\Service\JwtBlocklistInterface;
use App\Infrastructure\Http\Controller\ApiResponseTrait;
use App\Infrastructure\Http\Controller\ValidatesRequestTrait;
use App\Infrastructure\Security\JwtCookieManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Login / me / logout. No kernel exception listener - each action catches the
 * exceptions it can produce and maps the status itself.
 *
 * {{FILL: add password reset, registration, or whatever onboarding this project
 *  uses. Keep privileged account creation on the CLI, not on an endpoint.}}
 */
#[Route('/api/auth')]
final class AuthController extends AbstractController
{
    use ApiResponseTrait;
    use ValidatesRequestTrait;

    public function __construct(
        private readonly JwtCookieManager $jwtCookieManager,
    ) {
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request, LoginHandler $handler): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($request->getContent(), true) ?? [];

        $errors = [];

        if (!isset($data['email']) || !is_string($data['email']) || trim($data['email']) === '') {
            $errors['email'] = 'Email is required';
        }

        if (!isset($data['password']) || !is_string($data['password']) || $data['password'] === '') {
            $errors['password'] = 'Password is required';
        }

        if ($errors !== []) {
            return $this->validationError($errors);
        }

        try {
            $result = $handler->__invoke(new LoginInputDTO(
                email: (string) $data['email'],
                password: (string) $data['password'],
            ));

            // The token goes into the httpOnly cookie, never the body.
            $response = $this->success(['user' => $result->user->toArray()]);
            $response->headers->setCookie($this->jwtCookieManager->createCookie($result->token));

            return $response;
        } catch (InvalidCredentialsException $exception) {
            // Deliberately generic: never reveal whether the email or the password was wrong.
            return $this->error('Invalid email or password', $exception->getErrorCode(), 401);
        } catch (UserNotActiveException $exception) {
            return $this->error('Account is not active', $exception->getErrorCode(), 401);
        } catch (\InvalidArgumentException) {
            // Malformed email reaching the Email value object.
            return $this->error('Invalid email or password', 'INVALID_CREDENTIALS', 401);
        }
    }

    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->error('Unauthorized', 'UNAUTHORIZED', 401);
        }

        return $this->success(UserOutputDTO::fromEntity($user)->toArray());
    }

    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        JwtBlocklistInterface $jwtBlocklist,
        JWTEncoderInterface $jwtEncoder,
    ): JsonResponse {
        $token = $request->cookies->get(JwtCookieManager::COOKIE_NAME, '');

        if ($token === '') {
            $authHeader = $request->headers->get('Authorization', '');
            $token = str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : '';
        }

        if ($token === '') {
            return $this->error('No token provided', 'NO_TOKEN', 400);
        }

        try {
            $payload = $jwtEncoder->decode($token);
            $jti = $payload['jti'] ?? null;

            if ($jti === null) {
                return $this->error('Token has no JTI claim', 'INVALID_TOKEN', 400);
            }

            // Revoke for exactly the token's remaining lifetime - the blocklist
            // entry expires with it, so Redis never accumulates dead keys.
            $ttlSeconds = max(0, ($payload['exp'] ?? 0) - time());
            $jwtBlocklist->revoke((string) $jti, $ttlSeconds);

            $response = $this->success(['message' => 'Successfully logged out.']);
            $response->headers->setCookie($this->jwtCookieManager->createExpiredCookie());

            return $response;
        } catch (\Exception) {
            return $this->error('Invalid token', 'INVALID_TOKEN', 400);
        }
    }
}
