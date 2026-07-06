# Future Extensions

OTPAP v2 is intentionally conservative. Future versions MAY add:

- Key identifiers for managed key rotation
- Asymmetric signatures
- Device binding
- Proof-of-possession extensions
- Batch request envelopes
- Fine-grained token scopes
- Formal revocation receipts
- Structured audit attestations

## Compatibility Principle

Future changes SHOULD preserve the core idea that one token authorizes one request in one context exactly once.
