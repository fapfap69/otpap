<?php
/**
 * Server-side session manager for OTPAP.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class SessionManager
{
    /**
     * @var array<string, SessionContext>
     */
    private array $sessions = [];

    /**
     * Creates a new authenticated session.
     */
    public function createSession(string $applicationId, string $userId, int $ttlSeconds = 3600): SessionContext
    {
        $sessionId = 'sess_' . bin2hex(random_bytes(16));
        $session = new SessionContext(
            $applicationId,
            $sessionId,
            $userId,
            time() + $ttlSeconds
        );

        $this->sessions[$sessionId] = $session;

        return $session;
    }

    /**
     * Returns the session or null when unknown.
     */
    public function getSession(string $sessionId): ?SessionContext
    {
        $session = $this->sessions[$sessionId] ?? null;
        if ($session === null) {
            return null;
        }

        if ($session->getExpiresAt() < time()) {
            return null;
        }

        return $session;
    }

    /**
     * Binds a page identifier to an existing session.
     */
    public function bindPage(string $sessionId, string $pageId): void
    {
        $session = $this->requireSession($sessionId);
        $session->bindPage($pageId);
    }

    /**
     * Allocates the next monotonic sequence number for a session.
     */
    public function issueSequenceNumber(string $sessionId): int
    {
        $session = $this->requireSession($sessionId);
        return $session->issueSequenceNumber();
    }

    /**
     * Records that a sequence number has been consumed.
     */
    public function markConsumed(string $sessionId, int $sequenceNumber): void
    {
        $session = $this->requireSession($sessionId);
        $session->markConsumed($sequenceNumber);
    }

    /**
     * Revokes a session immediately.
     */
    public function revokeSession(string $sessionId): void
    {
        $session = $this->requireSession($sessionId);
        $session->revoke();
    }

    /**
     * Returns whether the token sequence can be accepted.
     */
    public function canAcceptSequence(string $sessionId, int $sequenceNumber): bool
    {
        $session = $this->getSession($sessionId);
        if ($session === null || $session->isRevoked()) {
            return false;
        }

        return $sequenceNumber > $session->getLastConsumedSequenceNumber();
    }

    /**
     * Returns the current session or throws a domain exception.
     */
    private function requireSession(string $sessionId): SessionContext
    {
        $session = $this->sessions[$sessionId] ?? null;
        if ($session === null) {
            throw new \RuntimeException('Unknown OTPAP session.');
        }

        return $session;
    }
}
