# Contributing to OTPAP

Thanks for helping improve OTPAP.

## What We Value

- Clear protocol language
- Security-first reasoning
- Reproducible examples
- Small, reviewable changes
- Backward-compatible evolution where possible

## Suggested Workflow

1. Read the RFC draft and the security considerations.
2. Open an issue or describe the change in a pull request.
3. Keep changes narrow and document protocol impact.
4. Add or update test vectors when behavior changes.
5. Verify the PHP reference implementation still passes validation.

## Documentation Rules

- Documentation MUST be written in English.
- Normative statements SHOULD use RFC language such as MUST, SHOULD, MAY, and MUST NOT.
- Examples SHOULD show the full request lifecycle when practical.

## Code Rules

- Keep PHP code typed and documented.
- Document every public class and method.
- Prefer explicit data structures over hidden global state.
- Preserve deterministic canonicalization and signature behavior.

## Review Notes

Security-related changes SHOULD include:

- Threat model impact
- Replay and tampering implications
- Backward compatibility notes
- Updated examples or schemas when needed
