<?php
/**
 * Immutable request context used to bind OTPAP tokens.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class RequestContext
{
    /**
     * Creates a request context that describes the protected request.
     */
    public function __construct(
        public readonly string $applicationId,
        public readonly string $sessionId,
        public readonly string $userId,
        public readonly string $pageId,
        public readonly string $apiId,
        public readonly string $httpMethod,
        public readonly string $body
    ) {
    }

    /**
     * Returns the context as an associative array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'applicationId' => $this->applicationId,
            'sessionId' => $this->sessionId,
            'userId' => $this->userId,
            'pageId' => $this->pageId,
            'apiId' => $this->apiId,
            'httpMethod' => $this->httpMethod,
            'body' => $this->body,
        ];
    }
}
