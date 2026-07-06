<?php
/**
 * High-level OTPAP orchestration engine.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class OTPAPEngine
{
    /**
     * Creates an OTPAP engine.
     */
    public function __construct(
        private readonly OTPAPGenerator $generator,
        private readonly OTPAPValidator $validator
    ) {
    }

    /**
     * Generates a token for the supplied request context.
     */
    public function generateToken(RequestContext $request, string $secret, int $ttlSeconds = 60): OtpapToken
    {
        return $this->generator->generate($request, $secret, $ttlSeconds);
    }

    /**
     * Validates a token for the supplied request context.
     */
    public function validateToken(OtpapToken $token, RequestContext $request, string $secret, ?int $now = null): ValidationResult
    {
        return $this->validator->validate($token, $request, $secret, $now);
    }
}
