# PHP Reference Implementation

This document explains the reference code found in `src/`.

## Autoloading

The file `src/autoload.php` registers a small PSR-4 style autoloader so the reference implementation can run without Composer. The project also ships a `composer.json` file for teams that prefer standard PHP package workflows.

## Class Overview

- `Crypto` implements canonical JSON encoding, SHA-256 body hashing, HMAC-SHA256 signing, and constant-time comparison.
- `RequestContext` captures the request that the token protects.
- `SessionContext` stores server-side session state, page binding, sequence ordering, and revocation state.
- `OtpapToken` represents the canonical token object and knows how to serialize itself.
- `ValidationResult` converts protocol decisions into structured validation output.
- `ReplayStore` defines the replay contract.
- `InMemoryReplayStore` provides a simple reference replay store.
- `SessionManager` creates, binds, revokes, and advances sessions.
- `OTPAPGenerator` signs tokens for a specific request context.
- `OTPAPValidator` verifies the token and consumes it exactly once.
- `OTPAPEngine` composes generator and validator.
- `ApiDispatcher` gates business handlers behind OTPAP validation.

## Example Usage

1. Create a `SessionManager` and a `ReplayStore`.
2. Create the `OTPAPGenerator` and `OTPAPValidator`.
3. Bind the session to the page that will issue the request.
4. Build a `RequestContext`.
5. Generate a token.
6. Send the token with the API request.
7. Validate and dispatch the request server-side.

## Security Notes

The reference implementation uses an in-memory replay store for clarity. Production deployments SHOULD use a strongly consistent store and SHOULD make token consumption atomic with request acceptance.
