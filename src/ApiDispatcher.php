<?php
/**
 * Dispatches validated OTPAP-protected requests.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class ApiDispatcher
{
    /**
     * Creates a dispatcher around the OTPAP engine.
     */
    public function __construct(
        private readonly OTPAPEngine $engine
    ) {
    }

    /**
     * Validates a request and executes the business handler on success.
     *
     * @param callable(RequestContext, OtpapToken): array<string, mixed> $handler
     * @return array<string, mixed>
     */
    public function dispatch(RequestContext $request, OtpapToken $token, string $secret, callable $handler, ?int $now = null): array
    {
        $result = $this->engine->validateToken($token, $request, $secret, $now);
        if (!$result->isValid()) {
            return [
                'valid' => false,
                'code' => $result->getCode(),
                'message' => $result->getMessage(),
                'consumed' => false,
                'tokenId' => null,
                'data' => null,
            ];
        }

        $data = $handler($request, $token);

        return [
            'valid' => true,
            'code' => $result->getCode(),
            'message' => $result->getMessage(),
            'consumed' => true,
            'tokenId' => $result->getTokenId(),
            'data' => $data,
        ];
    }
}
