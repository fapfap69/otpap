<?php
/**
 * OTPAP request validator.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class OTPAPValidator
{
    /**
     * Creates a new validator instance.
     */
    public function __construct(
        private readonly Crypto $crypto,
        private readonly SessionManager $sessionManager,
        private readonly ReplayStore $replayStore,
        private readonly int $allowedClockSkewSeconds = 5
    ) {
    }

    /**
     * Validates a token against the request context and consumes it on success.
     */
    public function validate(OtpapToken $token, RequestContext $request, string $secret, ?int $now = null): ValidationResult
    {
        $now ??= time();

        if ($token->protocolVersion !== '2.0') {
            return ValidationResult::failure('OTPAP-1001', 'Unsupported protocol version.');
        }

        $session = $this->sessionManager->getSession($token->sessionId);
        if ($session === null || $session->isRevoked()) {
            return ValidationResult::failure('OTPAP-1011', 'Session is invalid or revoked.');
        }

        if ($session->getApplicationId() !== $request->applicationId || $session->getSessionId() !== $request->sessionId || $session->getUserId() !== $request->userId) {
            return ValidationResult::failure('OTPAP-1005', 'Session binding failed.');
        }

        if ($session->getPageId() !== null && $session->getPageId() !== $token->pageId) {
            return ValidationResult::failure('OTPAP-1006', 'Page binding failed.');
        }

        if ($token->applicationId !== $request->applicationId || $token->sessionId !== $request->sessionId || $token->userId !== $request->userId) {
            return ValidationResult::failure('OTPAP-1005', 'Session or application binding failed.');
        }

        if ($token->pageId !== $request->pageId) {
            return ValidationResult::failure('OTPAP-1006', 'Page binding failed.');
        }

        if ($token->apiId !== $request->apiId) {
            return ValidationResult::failure('OTPAP-1007', 'API binding failed.');
        }

        if ($token->httpMethod !== strtoupper($request->httpMethod)) {
            return ValidationResult::failure('OTPAP-1008', 'HTTP method binding failed.');
        }

        if ($token->timestamp - $this->allowedClockSkewSeconds > $now || $token->expiration < $now) {
            return ValidationResult::failure('OTPAP-1004', 'Token is expired or not yet valid.');
        }

        $bodyHash = $this->crypto->hashBody($request->body);
        if (!$this->crypto->equals($token->bodyHash, $bodyHash)) {
            return ValidationResult::failure('OTPAP-1009', 'Body hash mismatch detected.');
        }

        $expectedSignature = $this->crypto->hmac($this->crypto->canonicalJson($token->payloadForSignature()), $secret);
        if (!$this->crypto->equals($token->signature, $expectedSignature)) {
            return ValidationResult::failure('OTPAP-1003', 'Signature verification failed.');
        }

        $tokenId = $token->tokenId();
        if ($this->replayStore->has($tokenId, $now)) {
            return ValidationResult::failure('OTPAP-1010', 'Replay detected.');
        }

        if (!$this->sessionManager->canAcceptSequence($token->sessionId, $token->sequenceNumber)) {
            return ValidationResult::failure('OTPAP-1012', 'Sequence number rejected.');
        }

        if (!$this->replayStore->consume($tokenId, $token->expiration, $now)) {
            return ValidationResult::failure('OTPAP-1010', 'Replay detected.');
        }

        $this->sessionManager->markConsumed($token->sessionId, $token->sequenceNumber);

        return ValidationResult::success($tokenId);
    }
}
