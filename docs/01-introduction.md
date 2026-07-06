# Introduction

OTPAP v2 is a request security protocol designed for authenticated server-side applications that need stronger guarantees than a bearer token can provide.

OTPAP is not a login token, identity token, or federated authorization grant. A user identity may already be established by a separate authentication mechanism. OTPAP operates inside that authenticated application context and binds a single request to a single, explicit execution intent.

## Problem Statement

Many applications rely on session cookies alone and then trust client-side code to submit requests safely. That model is vulnerable to:

- Replay attacks
- Request tampering
- Endpoint substitution
- Cross-page token reuse
- Request-body manipulation
- Token theft replay within a valid session

OTPAP addresses these risks by making every API call explicit, stateful, and short-lived.

## Core Idea

A server generates a one-time token for a specific request context. The token is bound to:

- The application
- The authenticated session
- The user
- The page that issued the request
- The API endpoint
- The HTTP method
- The request body
- A nonce and sequence number
- A timestamp and expiration window

The server validates the token before execution and consumes it immediately after a successful decision.

## Terminology

- **Application**: The server-side system using OTPAP.
- **Session**: An authenticated application session.
- **Page**: A server-generated browser context that may issue requests.
- **API**: The protected business operation.
- **Token**: The one-time OTPAP artifact.
- **Replay Store**: State used to reject reuse.

## Non-Goals

OTPAP does not:

- Replace user authentication
- Replace transport security
- Replace authorization policy
- Define browser storage rules
- Mandate a specific programming language

## Expected Deployment

OTPAP SHOULD be used with HTTPS, server-side session management, canonical request normalization, and strong secret management. It MAY be layered on top of existing application authentication and authorization controls.
