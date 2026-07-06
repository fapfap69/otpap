<?php
/**
 * Canonical OTPAP token object.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class OtpapToken implements \JsonSerializable
{
    /**
     * Creates a signed OTPAP token object.
     */
    public function __construct(
        public readonly string $protocolVersion,
        public readonly string $applicationId,
        public readonly string $sessionId,
        public readonly string $userId,
        public readonly string $pageId,
        public readonly string $apiId,
        public readonly string $httpMethod,
        public readonly string $nonce,
        public readonly int $sequenceNumber,
        public readonly int $timestamp,
        public readonly int $expiration,
        public readonly string $bodyHash,
        public readonly string $signature
    ) {
    }

    /**
     * Serializes the token as an associative array.
     *
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'ProtocolVersion' => $this->protocolVersion,
            'ApplicationId' => $this->applicationId,
            'SessionId' => $this->sessionId,
            'UserId' => $this->userId,
            'PageId' => $this->pageId,
            'ApiId' => $this->apiId,
            'HttpMethod' => $this->httpMethod,
            'Nonce' => $this->nonce,
            'SequenceNumber' => $this->sequenceNumber,
            'Timestamp' => $this->timestamp,
            'Expiration' => $this->expiration,
            'BodyHash' => $this->bodyHash,
            'Signature' => $this->signature,
        ];
    }

    /**
     * Returns the canonical signature payload without the signature field.
     *
     * @return array<string, int|string>
     */
    public function payloadForSignature(): array
    {
        $token = $this->toArray();
        unset($token['Signature']);
        return $token;
    }

    /**
     * Returns a deterministic token identifier for replay tracking.
     */
    public function tokenId(): string
    {
        return hash('sha256', json_encode($this->payloadForSignature(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
    }

    /**
     * Returns the JSON serialization used by json_encode.
     *
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
