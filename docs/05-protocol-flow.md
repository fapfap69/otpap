# Protocol Flow

OTPAP follows a strict server-led lifecycle.

## Flow

1. User Login
2. Authenticated Session
3. HTML Page Generation
4. OTPAP Generation
5. Client API Request
6. OTPAP Validation
7. Business API Execution
8. OTPAP Consumption
9. Replay Database Update

## Request Lifecycle

The server SHOULD generate the token as late as practical, ideally when rendering the page or returning a response that will immediately be acted upon. The token SHOULD be bound to the exact request body that the client is expected to submit.

## Failure Handling

Any mismatch in session, page, API, method, body hash, signature, expiration, or replay state MUST cause rejection before business execution.

## Pseudocode

```text
authenticate user
create or load session
render page with token
receive request and token
validate token against request context
if validation succeeds, execute business logic
consume token
record replay state
```
