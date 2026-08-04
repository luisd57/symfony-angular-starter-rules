<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Without JSON_PRESERVE_ZERO_FRACTION, json_encode emits 80.0 as 80 and every
     * float in the API silently becomes an int on the wire.
     *
     * Do NOT set this via JsonResponse::setEncodingOptions() after construction:
     * that re-encodes from the already-serialized string, by which point the zero
     * fraction is gone. The options must apply to the FIRST encode.
     */
    private const int ENCODING_OPTIONS = JsonResponse::DEFAULT_ENCODING_OPTIONS
        | JSON_PRESERVE_ZERO_FRACTION;

    protected function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return $this->json([
            'success' => true,
            'data' => $data,
        ], $status);
    }

    private function json(array $payload, int $status): JsonResponse
    {
        $response = new JsonResponse(null, $status);
        $response->setEncodingOptions(self::ENCODING_OPTIONS);
        $response->setData($payload);

        return $response;
    }

    protected function created(mixed $data = null): JsonResponse
    {
        return $this->success($data, 201);
    }

    protected function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }

    protected function error(string $message, string $code, int $status = 400): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }

    protected function validationError(array $errors): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validation failed',
                'details' => $errors,
            ],
        ], 422);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 'UNAUTHORIZED', 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 'FORBIDDEN', 403);
    }

    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 'NOT_FOUND', 404);
    }
}
