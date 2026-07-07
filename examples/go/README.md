# Go Hello World Example

This example uses the Go standard library only.

## Files

- `go.mod`
- `main.go`
- `otpap.go`

## Run

```bash
cd examples/go
go run .
```

## Endpoints

- `GET /page` returns a Hello World page payload and a token.
- `POST /api/hello` validates the token and returns `Hello World`.
