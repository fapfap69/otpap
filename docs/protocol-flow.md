# Protocol Flow

## Login

User authenticates.

Server creates:

- SessionId
- UserId

---

## Page Generation

Server creates:

- PageId
- OTPAP Token

---

## API Invocation

Client sends:

SessionId
PageId
OTPAP
Body

---

## Validation

OTPAP Engine verifies:

- Signature
- Expiration
- Nonce
- Sequence
- API Binding
- Page Binding
- Body Hash

---

## Execution

API is executed.

---

## Consumption

Token status becomes:

CONSUMED

Further use is rejected.

