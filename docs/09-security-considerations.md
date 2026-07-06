# Security Considerations

OTPAP reduces risk by forcing server-side binding of request intent to request execution.

## Replay Protection

Replay protection is mandatory. A token MUST be consumed only once, and the replay store SHOULD be strongly consistent for the protected operation.

## Request Tampering

Any change to the body, endpoint, method, or page context MUST invalidate the token.

## Session Hijacking

OTPAP cannot prevent session hijacking if the session itself is compromised. It DOES prevent reuse of an OTPAP token outside the intended context.

## Revocation

Immediate revocation SHOULD be available at the session layer and MAY be supported at the token layer. Once revoked, future validation MUST fail.

## Storage Guidance

Tokens SHOULD NOT be stored in long-lived browser storage. If client-side persistence is unavoidable, the window SHOULD be minimized and protected with standard web defenses.

## Logging

Logs MUST avoid exposing raw secrets. If token telemetry is necessary, log hashes or truncated identifiers instead of raw signatures or body hashes.
