<?php
/**
 * Mutable server-side session state for OTPAP.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class SessionContext
{
    private ?string $pageId;
    private int $lastConsumedSequenceNumber;
    private int $nextSequenceNumber;
    private bool $revoked;

    /**
     * Creates a server-side session context.
     */
    public function __construct(
        private readonly string $applicationId,
        private readonly string $sessionId,
        private readonly string $userId,
        private readonly int $expiresAt,
        ?string $pageId = null,
        int $lastConsumedSequenceNumber = 0,
        int $nextSequenceNumber = 1,
        bool $revoked = false
    ) {
        $this->pageId = $pageId;
        $this->lastConsumedSequenceNumber = $lastConsumedSequenceNumber;
        $this->nextSequenceNumber = $nextSequenceNumber;
        $this->revoked = $revoked;
    }

    /**
     * Returns the application identifier.
     */
    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    /**
     * Returns the session identifier.
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * Returns the user identifier.
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Returns the page currently bound to the session, if any.
     */
    public function getPageId(): ?string
    {
        return $this->pageId;
    }

    /**
     * Returns the session expiration time.
     */
    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    /**
     * Returns the last consumed sequence number.
     */
    public function getLastConsumedSequenceNumber(): int
    {
        return $this->lastConsumedSequenceNumber;
    }

    /**
     * Returns the next sequence number to issue.
     */
    public function getNextSequenceNumber(): int
    {
        return $this->nextSequenceNumber;
    }

    /**
     * Returns whether the session has been revoked.
     */
    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    /**
     * Binds the session to a page identifier.
     */
    public function bindPage(string $pageId): void
    {
        $this->pageId = $pageId;
    }

    /**
     * Marks the session revoked.
     */
    public function revoke(): void
    {
        $this->revoked = true;
    }

    /**
     * Allocates the next monotonic sequence number.
     */
    public function issueSequenceNumber(): int
    {
        return $this->nextSequenceNumber++;
    }

    /**
     * Records the latest consumed sequence number.
     */
    public function markConsumed(int $sequenceNumber): void
    {
        $this->lastConsumedSequenceNumber = max($this->lastConsumedSequenceNumber, $sequenceNumber);
    }
}
