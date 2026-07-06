# Internet-Draft: OTPAP v2

## Abstract

OTPAP, the One Time Password Application Protocol, is a stateful request-authentication protocol for server-side applications. OTPAP is designed to protect a single API request inside an already authenticated application execution context. It is not JWT, it is not an identity token, and it is not a generic authorization grant.

OTPAP uses short-lived tokens, canonical serialization, HMAC-SHA256 signatures, replay-state tracking, and contextual binding to the authenticated session, page, API operation, HTTP method, and request body.

## Introduction

Web applications often need to protect business actions that are initiated from a server-rendered page or from a tightly controlled application context. Session cookies alone do not express which request was intended, and bearer-style tokens can be replayed when they are stolen or reused.

OTPAP addresses that gap by making the request itself the protected object. The protocol binds the token to:

- the application deployment,
- the authenticated session,
- the authenticated user,
- the page context that issued the request,
- the target API operation,
- the HTTP method,
- the request body,
- a nonce,
- a monotonic sequence number,
- an issuance timestamp,
- an expiration time.

The protocol is intentionally simple enough to implement in any language while still remaining strongly stateful.

## Goals

The protocol MUST:

- Prevent replay of accepted requests.
- Prevent tampering with the request body.
- Prevent API switching.
- Prevent method switching.
- Prevent page switching.
- Prevent session-bound token reuse outside the intended session.
- Support immediate revocation.
- Support short-lived tokens.
- Use HMAC-SHA256 for authentication.

The protocol SHOULD:

- Be easy to implement in server-side web frameworks.
- Be deterministic and auditable.
- Expose clear failure codes.
- Support language-independent validation rules.

The protocol MAY:

- Add key identifiers in future versions.
- Add optional claim extensions.
- Support multi-token batching in future revisions.

The protocol MUST NOT:

- Rely on client trust for security decisions.
- Allow a token to be accepted more than once.
- Depend on JWT semantics.

## Architecture

OTPAP divides responsibility across trusted server-side components.

### Authentication Layer

The authentication layer establishes identity and creates an application session.

### Session Manager

The session manager stores the session, tracks revocation, and maintains sequence state.

### Page Renderer

The page renderer issues tokens bound to the current page context.

### Generator

The generator constructs a canonical token representation and signs it with HMAC-SHA256.

### Validator

The validator verifies signature, freshness, binding, and replay state.

### Replay Store

The replay store records consumed token identifiers and MUST reject duplicates.

### Dispatcher

The dispatcher executes the business handler only after successful validation.

## Trust Model

Trusted:

- Application server
- Session store
- Replay store
- HMAC secret material

Untrusted:

- Browser
- JavaScript runtime in the browser
- Network
- Client storage

The protocol MUST assume that tokens can be observed, copied, and replayed by an attacker. Security therefore depends on binding and state.

## Threat Model

An attacker MAY:

- Copy a valid token from memory, logs, or transport traces.
- Replay a previously accepted request.
- Modify a request body after token issuance.
- Change the endpoint or HTTP method.
- Move the token into a different page or session.
- Submit duplicate requests in a race.

The attacker MUST NOT be assumed to know the HMAC secret or control the trusted server state.

## Cryptographic Requirements

### HMAC

OTPAP signatures MUST use HMAC-SHA256.

### Canonicalization

The token input to the signature algorithm MUST be canonical. Implementations SHOULD use sorted JSON object keys, UTF-8 encoding, and no insignificant whitespace.

### Randomness

Nonces MUST be generated using a cryptographically secure random source.

### Comparison

Signature comparison MUST be performed in constant time.

## Protocol Overview

OTPAP follows a fixed flow:

1. The user logs in.
2. The application creates an authenticated session.
3. The server renders a page.
4. The server creates an OTPAP token for a specific request.
5. The client sends the API request with the token.
6. The server validates the token against the current execution context.
7. The business operation executes.
8. The token is consumed.
9. The replay database updates the consumed state.

## Token Format

An OTPAP token MUST contain the following fields:

- `ProtocolVersion`
- `ApplicationId`
- `SessionId`
- `UserId`
- `PageId`
- `ApiId`
- `HttpMethod`
- `Nonce`
- `SequenceNumber`
- `Timestamp`
- `Expiration`
- `BodyHash`
- `Signature`

The `Signature` field is the HMAC-SHA256 digest of the canonical token representation excluding `Signature`.

### Normative Field Rules

`ProtocolVersion` MUST identify the protocol revision. `ApplicationId`, `SessionId`, `UserId`, and `PageId` MUST bind the token to the intended trusted context. `ApiId` and `HttpMethod` MUST bind the request target. `Nonce` and `SequenceNumber` MUST make duplicate acceptance detectable. `Timestamp` and `Expiration` MUST constrain lifetime. `BodyHash` MUST bind the payload.

## Protocol Flow

### Issuance

The server SHOULD generate the token as late as possible before delivery to the client. The issuance time SHOULD be close to the actual use time to reduce the attack window.

### Submission

The client MUST include the token with exactly one request.

### Validation

The server MUST validate:

- token structure,
- protocol version,
- session match,
- user match,
- page match,
- API match,
- method match,
- body hash match,
- timestamp freshness,
- expiration,
- signature,
- replay state,
- sequence ordering.

### Consumption

If validation succeeds, the token MUST be consumed before or atomically with business execution.

## Validation Algorithm

An implementation SHOULD perform validation in the following order:

1. Parse and normalize the token.
2. Verify required fields.
3. Resolve the authenticated session.
4. Compare application, session, user, page, API, and method bindings.
5. Verify timestamp and expiration.
6. Recompute the body hash and compare it to the token.
7. Recompute the canonical signature input.
8. Verify the HMAC-SHA256 signature with constant-time comparison.
9. Check the replay store for prior use.
10. Consume the token and persist the consumed state.

## Replay Protection

Replay prevention is mandatory. A token MUST be identified by a stable token identifier derived from the contextual fields and nonce or by an equivalent replay key. The replay store MUST reject any token identifier that has already been consumed.

Implementations SHOULD make replay state updates atomic with acceptance of the request to avoid a race that permits duplicate execution.

## Error Handling

Validation errors SHOULD be explicit enough for operators to diagnose but not so verbose that they reveal secret material. Error codes SHOULD map to a stable taxonomy such as invalid signature, expiration failure, body mismatch, or replay detection.

## Security Considerations

OTPAP provides defense in depth, not magical protection against a compromised server or a stolen session. It is strongest when combined with:

- HTTPS
- Secure session cookies
- Server-side session storage
- Strict canonicalization
- Immediate replay state updates
- Key rotation
- Audit logging

## Comparison with JWT

JWT primarily represents identity or claims. OTPAP represents a specific request intention and MUST be consumed exactly once. JWT is often used as a bearer artifact; OTPAP MUST NOT be used that way.

## Comparison with OAuth2

OAuth2 is a delegation framework and authorization framework. OTPAP does not replace OAuth2. A system MAY use OAuth2 for delegated access and OTPAP for request-level execution control. The two protocols solve different problems.

## Comparison with API Keys

API keys identify an application or integration but usually do not bind a request to a page context, body, or one-time execution requirement. OTPAP provides request-level integrity and replay protection that API keys do not provide.

## Future Extensions

Future versions MAY add:

- Key identifiers
- Multiple signature algorithms
- Device binding
- Attested session state
- Structured token revocation receipts

Any extension MUST preserve one-time request semantics.

## Appendix A. Canonical Example

```json
{
  "ProtocolVersion": "2.0",
  "ApplicationId": "warehouse",
  "SessionId": "sess_123",
  "UserId": "user_42",
  "PageId": "page_orders",
  "ApiId": "orders.create",
  "HttpMethod": "POST",
  "Nonce": "b4f2c0c1f2b544f6",
  "SequenceNumber": 18,
  "Timestamp": 1740000000,
  "Expiration": 1740000060,
  "BodyHash": "a3f5d5b3d2c1e0f19c7fdc6c3c65a4b23a1c2f0e8c4d3b1f0f0c2f7d8e9a0b1c2",
  "Signature": "5e9f9b3c8d5b1c0a7e1f2a3b4c5d6e7f8a9b0c1d2e3f405060708090a0b0c0d"
}
```

## Appendix B. Validation Pseudocode

```text
if token missing required fields: reject
if session invalid or revoked: reject
if bindings do not match request context: reject
if body hash mismatch: reject
if token is expired or too old: reject
if signature invalid: reject
if replay key already consumed: reject
consume token
execute business handler
```

## References

- RFC 2119
- NIST guidance on HMAC and SHA-256 usage
- OWASP guidance on session management and replay resistance
