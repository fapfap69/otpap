# Reference Implementation

The PHP reference implementation demonstrates the protocol in a small, dependency-free codebase.

## Classes

- `Crypto` handles hashing, HMAC, and canonicalization.
- `SessionManager` creates, binds, and revokes sessions.
- `ReplayStore` prevents duplicate execution.
- `OTPAPGenerator` creates tokens for a specific request.
- `OTPAPValidator` validates the token against request context.
- `OTPAPEngine` combines generation and validation.
- `ApiDispatcher` executes a handler only after validation.

## Design Goals

The reference implementation SHOULD be easy to read before it is easy to extend. It MUST keep token signing deterministic and validation explicit.

## Usage Pattern

1. Create a session.
2. Bind the page context.
3. Generate a token for a specific API request.
4. Attach the token to the client request.
5. Validate the token on the server.
6. Execute and consume the request exactly once.

## Extension Strategy

Languages other than PHP SHOULD preserve the same canonical fields, the same signature input, and the same state transitions.
