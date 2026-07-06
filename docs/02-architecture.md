# Architecture

OTPAP is built around a small set of trusted server-side components.

## Components

- **Authentication Layer**: Establishes the user session.
- **Session Manager**: Stores session state and revocation status.
- **Page Renderer**: Issues page-bound OTPAP tokens.
- **OTPAP Generator**: Produces signed one-time tokens.
- **OTPAP Validator**: Verifies token integrity and context binding.
- **Replay Store**: Records consumed token identifiers.
- **API Dispatcher**: Routes validated requests to business handlers.
- **Business Service**: Executes the protected operation.

## Trust Boundaries

The browser and network are untrusted. Token creation and validation MUST happen server-side. The client MAY carry the token but MUST NOT be trusted to preserve correctness.

## Data Flow

1. Authentication creates a session.
2. The server generates a page context.
3. The page renderer asks the generator for a token.
4. The client submits the token with one API request.
5. The validator checks the session, page, API, method, body, and replay state.
6. The dispatcher executes business logic only after successful validation.
7. The replay store marks the token as consumed.

## Architectural Properties

OTPAP SHOULD be deployed with:

- Centralized session state
- Deterministic token canonicalization
- Immediate replay marking
- Short token lifetimes
- Cryptographic key rotation procedures
