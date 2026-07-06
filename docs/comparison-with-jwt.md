# OTPAP vs JWT

| Feature | JWT | OTPAP |
|----------|----------|----------|
| Stateless | Yes | No |
| Session Binding | Optional | Mandatory |
| Page Binding | No | Yes |
| API Binding | No | Yes |
| Body Binding | No | Yes |
| Replay Prevention | Partial | Native |
| One-Time Use | No | Yes |
| Immediate Revocation | Difficult | Native |

## Conceptual Difference

JWT answers:

"Who are you?"

OTPAP answers:

"Are you authorized to perform this exact action in this exact context right now?"

