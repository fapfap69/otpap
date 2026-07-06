<?php
/**
 * In-memory replay store for the reference implementation.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class InMemoryReplayStore implements ReplayStore
{
    /**
     * @var array<string, int>
     */
    private array $consumed = [];

    /**
     * Returns whether the token has already been consumed.
     */
    public function has(string $tokenId, int $now): bool
    {
        $this->purgeExpired($now);
        return array_key_exists($tokenId, $this->consumed);
    }

    /**
     * Marks the token as consumed if it has not been seen before.
     */
    public function consume(string $tokenId, int $expiresAt, int $now): bool
    {
        $this->purgeExpired($now);
        if (array_key_exists($tokenId, $this->consumed)) {
            return false;
        }

        $this->consumed[$tokenId] = $expiresAt;
        return true;
    }

    /**
     * Removes expired replay entries.
     */
    private function purgeExpired(int $now): void
    {
        foreach ($this->consumed as $tokenId => $expiresAt) {
            if ($expiresAt < $now) {
                unset($this->consumed[$tokenId]);
            }
        }
    }
}
