<?php
/**
 * Cryptographic helper functions for OTPAP.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class Crypto
{
    /**
     * Generates a cryptographically secure random hex string.
     */
    public function randomHex(int $bytes = 16): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Computes the SHA-256 body hash as lowercase hex.
     */
    public function hashBody(string $body): string
    {
        return hash('sha256', $body);
    }

    /**
     * Produces a deterministic canonical JSON representation.
     *
     * Associative arrays are key-sorted recursively. Lists preserve order.
     */
    public function canonicalJson(array $value): string
    {
        return json_encode($this->normalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
    }

    /**
     * Computes an HMAC-SHA256 digest as lowercase hex.
     */
    public function hmac(string $message, string $secret): string
    {
        return hash_hmac('sha256', $message, $secret);
    }

    /**
     * Compares two strings using constant-time semantics.
     */
    public function equals(string $left, string $right): bool
    {
        return hash_equals($left, $right);
    }

    /**
     * Normalizes a token array for signature generation.
     *
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private function normalize(array $value): array
    {
        if (array_is_list($value)) {
            $normalized = [];
            foreach ($value as $item) {
                $normalized[] = is_array($item) ? $this->normalize($item) : $item;
            }
            return $normalized;
        }

        ksort($value);
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->normalize($item);
            }
        }

        return $value;
    }
}
