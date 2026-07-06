# Threat Model

## Replay Attack

Attacker reuses a captured request.

Mitigation:

- Nonce
- Sequence Number
- One-Time Token

---

## Body Manipulation

Attacker modifies payload.

Mitigation:

- SHA256 BodyHash
- HMAC Signature

---

## API Switching

Attacker changes endpoint.

Mitigation:

- ApiId Binding

---

## Context Hijacking

Attacker invokes request outside original page.

Mitigation:

- PageId Binding

---

## Session Theft

Attacker steals session cookie.

Mitigation:

- Session Binding
- Short Token Lifetime

