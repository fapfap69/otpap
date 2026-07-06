<?php
/**
 * Validation outcome for OTPAP request processing.
 */

declare(strict_types=1);

namespace Otpap\Reference;

final class ValidationResult
{
    /**
     * Creates a validation result.
     */
    public function __construct(
        private readonly bool $valid,
        private readonly string $code,
        private readonly string $message,
        private readonly ?string $tokenId = null
    ) {
    }

    /**
     * Returns a successful validation result.
     */
    public static function success(string $tokenId): self
    {
        return new self(true, 'OTPAP-0000', 'Token validated and consumed.', $tokenId);
    }

    /**
     * Returns a failed validation result.
     */
    public static function failure(string $code, string $message): self
    {
        return new self(false, $code, $message);
    }

    /**
     * Indicates whether the token was accepted.
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Returns the OTPAP error code.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns the validation message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the replay token identifier, if available.
     */
    public function getTokenId(): ?string
    {
        return $this->tokenId;
    }

    /**
     * Serializes the result as a response payload.
     *
     * @return array<string, bool|string|null>
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'code' => $this->code,
            'message' => $this->message,
            'consumed' => $this->valid,
            'tokenId' => $this->tokenId,
        ];
    }
}
