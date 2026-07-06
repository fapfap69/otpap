<?php
/**
 * OTPAP token generator.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class OTPAPGenerator
{
    /**
     * Creates a new OTPAP generator.
     */
    public function __construct(
        private readonly Crypto $crypto,
        private readonly SessionManager $sessionManager
    ) {
    }

    /**
     * Generates a signed token for the supplied request context.
     */
    public function generate(RequestContext $request, string $secret, int $ttlSeconds = 60): OtpapToken
    {
        $session = $this->sessionManager->getSession($request->sessionId);
        if ($session === null) {
            throw new \RuntimeException('Cannot generate OTPAP token for an unknown session.');
        }

        if ($session->getApplicationId() !== $request->applicationId || $session->getUserId() !== $request->userId) {
            throw new \RuntimeException('Request context does not match the active session.');
        }

        if ($session->getPageId() !== null && $session->getPageId() !== $request->pageId) {
            throw new \RuntimeException('Request page is not bound to the active session.');
        }

        $sequenceNumber = $this->sessionManager->issueSequenceNumber($request->sessionId);
        $timestamp = time();
        $expiration = $timestamp + $ttlSeconds;
        $bodyHash = $this->crypto->hashBody($request->body);
        $nonce = $this->crypto->randomHex(8);

        $unsignedToken = new OtpapToken(
            '2.0',
            $request->applicationId,
            $request->sessionId,
            $request->userId,
            $request->pageId,
            $request->apiId,
            $request->httpMethod,
            $nonce,
            $sequenceNumber,
            $timestamp,
            $expiration,
            $bodyHash,
            ''
        );

        $signature = $this->crypto->hmac($this->crypto->canonicalJson($unsignedToken->payloadForSignature()), $secret);

        return new OtpapToken(
            $unsignedToken->protocolVersion,
            $unsignedToken->applicationId,
            $unsignedToken->sessionId,
            $unsignedToken->userId,
            $unsignedToken->pageId,
            $unsignedToken->apiId,
            $unsignedToken->httpMethod,
            $unsignedToken->nonce,
            $unsignedToken->sequenceNumber,
            $unsignedToken->timestamp,
            $unsignedToken->expiration,
            $unsignedToken->bodyHash,
            $signature
        );
    }
}
