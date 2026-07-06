# OTPAP v2

OTPAP v2, the One Time Password Application Protocol, is a stateful request-authentication protocol for server-side web applications.

OTPAP is **not** JWT, and it is **not** an identity token. It is a request-bound authorization artifact that protects a single API invocation inside an already authenticated application session.

## What OTPAP Provides

- One-time API tokens
- Stateful validation and replay prevention
- Session binding
- Page binding
- API binding
- HTTP method binding
- Request body binding
- Immediate token revocation
- Short-lived tokens
- HMAC-SHA256 authentication

## Repository Status

This repository contains the first public draft of the specification and a functional PHP reference implementation. The language of all documentation is English and the repository is intended to be published as `v2.0.0-draft`.

## Documentation

- [RFC draft](rfc/draft-otpap-v2.md)
- [Introduction](docs/01-introduction.md)
- [Architecture](docs/02-architecture.md)
- [Threat Model](docs/03-threat-model.md)
- [Token Format](docs/04-token-format.md)
- [Protocol Flow](docs/05-protocol-flow.md)
- [State Machine](docs/06-state-machine.md)
- [Cryptography](docs/07-cryptography.md)
- [Error Codes](docs/08-error-codes.md)
- [Security Considerations](docs/09-security-considerations.md)
- [Comparison with JWT](docs/10-comparison-with-jwt.md)
- [Reference Implementation](docs/11-reference-implementation.md)
- [Future Extensions](docs/12-future-extensions.md)

## Repository Layout

- `src/` contains the PHP reference library.
- `examples/` contains multi-language usage examples.
- `schemas/` contains JSON Schemas for tokens, requests, and responses.
- `diagrams/` contains draw.io diagrams for the protocol.
- `tests/` contains normative test vectors and PHP validation tests.

## Security Model

OTPAP assumes the browser, network, and client-side JavaScript are not trusted. The server-side application, session store, replay store, and secret material are trusted.

The protocol binds each request to a specific authenticated session, a specific page context, a specific API operation, a specific HTTP method, and a specific request body. A token MUST be consumed exactly once.

## Quick Start

1. Read the RFC draft.
2. Review the token format and security model.
3. Use the PHP reference implementation as the baseline.
4. Adapt the token structure and validation algorithm to your language and framework.

## Example Flow

1. User logs in.
2. The server creates an authenticated session.
3. The server renders a page and emits an OTPAP token.
4. The client submits one API request with that token.
5. The server validates, executes, and consumes the token.
6. Any replay attempt fails.

## License

Distributed under the MIT License. See [LICENSE](LICENSE).
