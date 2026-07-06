<?php
/**
 * Replay store contract for OTPAP token consumption.
 */

declare(strict_types=1);

namespace Otpap\Reference;

interface ReplayStore
{
    /**
     * Returns whether the token has already been consumed.
     */
    public function has(string $tokenId, int $now): bool;

    /**
     * Marks the token as consumed if it has not been seen before.
     */
    public function consume(string $tokenId, int $expiresAt, int $now): bool;
}
