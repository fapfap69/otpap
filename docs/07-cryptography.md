# Cryptography

OTPAP relies on symmetric authentication with HMAC-SHA256 and canonical data encoding.

## Required Primitives

- HMAC-SHA256 for token signatures
- SHA-256 for request-body hashing
- Constant-time equality comparison
- Cryptographically secure random nonce generation

## Signing Input

The signature MUST be computed over the canonical token representation excluding the `Signature` field. The canonical representation SHOULD include all context fields in a stable order.

## Body Hashing

The `BodyHash` field SHOULD be the SHA-256 digest of the exact request body bytes or a documented canonicalized form of the body. Applications that use JSON bodies MUST define a stable JSON canonicalization rule.

## Key Management

- Keys SHOULD be stored outside source control.
- Keys SHOULD rotate on a planned schedule.
- Key identifiers MAY be introduced in future versions.
- A rotated key MUST NOT invalidate in-flight validation without an intentional migration strategy.

## Comparison to Asymmetric Models

OTPAP intentionally uses HMAC to keep the protocol simple for server-to-server and server-to-browser request validation. Asymmetric signing MAY be added later, but it is not required for the draft.
