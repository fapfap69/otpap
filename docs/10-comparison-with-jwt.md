# Comparison with JWT

OTPAP and JWT solve different problems.

## JWT

JWT is commonly used to represent identity or claims about a subject. It is often treated as a bearer artifact.

## OTPAP

OTPAP is a one-time request authorization artifact. It is designed to prove that a specific request was generated inside a specific authenticated execution context.

## Key Differences

| Property | JWT | OTPAP |
|----------|-----|-------|
| Primary purpose | Identity/claims | Request authorization |
| Lifetime | Often longer | Short-lived |
| Reuse | Common as bearer token | MUST be one-time |
| Binding | Usually subject-centric | Session, page, API, method, body |
| Validation | Often stateless | Stateful |
| Replay resistance | Depends on deployment | Mandatory |

## Interoperability

OTPAP MAY coexist with JWT or other identity systems. In such a deployment, JWT establishes who the user is, and OTPAP establishes whether a specific API request SHOULD execute.
