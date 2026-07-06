# Token Format

OTPAP tokens are canonical JSON objects containing all context required to authenticate one request.

## Required Fields

- `ProtocolVersion`
- `ApplicationId`
- `SessionId`
- `UserId`
- `PageId`
- `ApiId`
- `HttpMethod`
- `Nonce`
- `SequenceNumber`
- `Timestamp`
- `Expiration`
- `BodyHash`
- `Signature`

## Field Semantics

- `ProtocolVersion` identifies the protocol revision.
- `ApplicationId` identifies the application deployment.
- `SessionId` binds the token to the authenticated session.
- `UserId` binds the token to the principal.
- `PageId` binds the token to the page or view that issued it.
- `ApiId` identifies the business endpoint.
- `HttpMethod` binds the allowed method.
- `Nonce` ensures uniqueness.
- `SequenceNumber` enforces stateful ordering.
- `Timestamp` records issuance time.
- `Expiration` limits validity.
- `BodyHash` binds the payload.
- `Signature` authenticates the canonical token data.

## Canonicalization

The token MUST be serialized into a canonical UTF-8 JSON representation with stable key ordering before signature calculation. Implementations SHOULD treat the canonical form as the only acceptable signature input.

## Example Shape

```json
{
  "ProtocolVersion": "2.0",
  "ApplicationId": "warehouse",
  "SessionId": "sess_123",
  "UserId": "user_42",
  "PageId": "page_orders",
  "ApiId": "orders.create",
  "HttpMethod": "POST",
  "Nonce": "b4f2c0c1f2b544f6",
  "SequenceNumber": 18,
  "Timestamp": 1740000000,
  "Expiration": 1740000060,
  "BodyHash": "a3f5d5b3d2c1e0f19c7fdc6c3c65a4b23a1c2f0e8c4d3b1f0f0c2f7d8e9a0b1c2",
  "Signature": "5e9f9b3c8d5b1c0a7e1f2a3b4c5d6e7f8a9b0c1d2e3f405060708090a0b0c0d"
}
```

## Validation Rules

The validator MUST reject any token missing a required field, containing an invalid timestamp, containing an expired expiration value, or failing signature verification.
