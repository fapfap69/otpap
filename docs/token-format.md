# OTPAP Token Format

## Fields

| Field | Description |
|---------|-------------|
| ProtocolVersion | Protocol version |
| ApplicationId | Application identifier |
| SessionId | Session identifier |
| UserId | User identifier |
| PageId | Page identifier |
| ApiId | Target API |
| HttpMethod | HTTP method |
| Nonce | Cryptographic random value |
| SequenceNumber | Request sequence |
| Timestamp | Token generation time |
| Expiration | Expiration time |
| BodyHash | Request body digest |
| Signature | HMAC signature |

---

## Example

{
  "ProtocolVersion":"2.0",
  "ApplicationId":"Warehouse",
  "SessionId":"S12345",
  "UserId":"antonio",
  "PageId":"P67890",
  "ApiId":"movimenti/scaricoutenti",
  "HttpMethod":"POST",
  "Nonce":"f8ac13...",
  "SequenceNumber":17,
  "Timestamp":1740000000,
  "Expiration":1740000060,
  "BodyHash":"bff3...",
  "Signature":"a882..."
}

