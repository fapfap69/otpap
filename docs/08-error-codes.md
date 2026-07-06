# Error Codes

OTPAP error codes are designed to be explicit enough for operational telemetry while still preventing unnecessary disclosure.

| Code | Meaning | Typical Cause |
|------|---------|---------------|
| `OTPAP-0000` | Success | Token validated and consumed |
| `OTPAP-1001` | Invalid format | Malformed token object |
| `OTPAP-1002` | Missing field | Required field absent |
| `OTPAP-1003` | Invalid signature | HMAC mismatch |
| `OTPAP-1004` | Expired token | Timestamp outside validity window |
| `OTPAP-1005` | Session mismatch | Token not bound to the active session |
| `OTPAP-1006` | Page mismatch | Token not bound to the issuing page |
| `OTPAP-1007` | API mismatch | Token not bound to the target API |
| `OTPAP-1008` | Method mismatch | HTTP method differs from token |
| `OTPAP-1009` | Body mismatch | Body hash differs |
| `OTPAP-1010` | Replay detected | Token already consumed |
| `OTPAP-1011` | Revoked token | Session or token revoked |
| `OTPAP-1012` | Sequence rejected | Sequence is stale or out of order |

## Operational Guidance

Applications SHOULD log these codes with request correlation identifiers. They SHOULD NOT echo sensitive token material back to the client.
