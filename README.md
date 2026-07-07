# OTPAP v2

[![Build](https://github.com/fapfap69/otpap/actions/workflows/build.yml/badge.svg)](https://github.com/fapfap69/otpap/actions/workflows/build.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Draft](https://img.shields.io/badge/status-v2.0.0--draft-blue.svg)](CHANGELOG.md)

OTPAP v2, the One Time Password Application Protocol, is a stateful request-authentication protocol for server-side web applications.

OTPAP is **not** JWT, and it is **not** an identity token. It is a request-bound authorization artifact that protects a single API invocation inside an already authenticated application session.

## Why OTPAP Exists

Most application stacks already have login, sessions, and role-based authorization. What they often lack is a native way to bind a specific business action to:

- one authenticated session
- one page or execution context
- one API endpoint
- one HTTP method
- one request body
- one successful execution only

OTPAP fills that gap with short-lived, stateful, HMAC-authenticated tokens.

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

## Quick Start

1. Read the [RFC draft](rfc/draft-otpap-v2.md).
2. Review the [token format](docs/04-token-format.md) and [protocol flow](docs/05-protocol-flow.md).
3. Run the PHP reference implementation in [`src/`](src/).
4. Open one of the runnable examples in [`examples/`](examples/).

## Live Areas

- [PHP reference implementation](reference/php-reference-implementation.md)
- [JavaScript browser example](examples/javascript/README.md)
- [Node.js Express example](examples/nodejs/README.md)
- [Python FastAPI example](examples/python/README.md)
- [Java Spring Boot example](examples/java/README.md)
- [Go example](examples/go/README.md)
- [ASP.NET Core example](examples/csharp/README.md)

## Documentation

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
- `examples/` contains runnable Hello World integrations for multiple languages.
- `schemas/` contains JSON Schemas for tokens, requests, and responses.
- `diagrams/` contains draw.io diagrams for the protocol.
- `tests/` contains normative test vectors and PHP validation tests.

## Security Model

OTPAP assumes the browser, network, and client-side JavaScript are not trusted. The server-side application, session store, replay store, and secret material are trusted.

The protocol binds each request to a specific authenticated session, a specific page context, a specific API operation, a specific HTTP method, and a specific request body. A token MUST be consumed exactly once.

## Example Flow

1. User logs in.
2. The server creates an authenticated session.
3. The server renders a page and emits an OTPAP token.
4. The client submits one API request with that token.
5. The server validates, executes, and consumes the token.
6. Any replay attempt fails.

## For Contributors

- Read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.
- Report security issues through [SECURITY.md](SECURITY.md).
- Use the existing test vectors when changing protocol behavior.
- Keep documentation in English and use RFC language for normative rules.

## Release Status

This repository is intended to be published as `v2.0.0-draft`.

## License

Distributed under the MIT License. See [LICENSE](LICENSE).
