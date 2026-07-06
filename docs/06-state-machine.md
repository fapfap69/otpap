# State Machine

OTPAP tokens move through a small, auditable state machine.

## States

- `ISSUED`
- `PRESENTED`
- `VALIDATING`
- `CONSUMED`
- `REJECTED`
- `REVOKED`

## Transitions

- `ISSUED` to `PRESENTED` when the client receives the token.
- `PRESENTED` to `VALIDATING` when the server receives the request.
- `VALIDATING` to `CONSUMED` when validation succeeds and execution is authorized.
- `VALIDATING` to `REJECTED` when any validation check fails.
- Any non-consumed state to `REVOKED` when the session or token is revoked server-side.

## Invariants

- A consumed token MUST NOT be accepted again.
- A revoked token MUST NOT be accepted.
- A rejected token MUST NOT become consumable.

## Ordering Rule

Sequence numbers SHOULD increase monotonically within a session so that duplicate or stale submissions can be detected even before body validation completes.
