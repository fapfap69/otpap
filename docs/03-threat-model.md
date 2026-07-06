# Threat Model

OTPAP assumes an active attacker may observe, alter, replay, or substitute client-originated requests. The protocol is designed to fail closed under those conditions.

## Attacker Capabilities

An attacker MAY:

- Steal a token from browser memory or logs
- Replay a previously valid request
- Modify the request body
- Change the target API endpoint
- Change the HTTP method
- Move a token between pages or sessions
- Attempt duplicate submission races

An attacker MUST NOT be assumed to control server-side secrets, session storage, or the replay store.

## Security Objectives

The protocol MUST prevent:

- Replay attack success after first consumption
- Body tampering
- API switching
- Page switching
- Session hijacking reuse of tokens outside the bound session
- Duplicate request execution
- Context switching across pages or endpoints

## Residual Risk

OTPAP does not protect against:

- Compromised server-side secret material
- Malicious server logic
- Exfiltration of the authenticated session itself
- Side-channel leakage outside the protocol design

## Assumptions

The server MUST maintain accurate session state, and the replay store SHOULD be strongly consistent for the protected operation. If consistency is weakened, the risk of duplicate acceptance increases.
