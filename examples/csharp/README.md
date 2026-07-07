# ASP.NET Core Hello World Example

This example uses the ASP.NET Core minimal hosting model.

## Files

- `Program.cs`
- `Otpap.cs`

## Run

```bash
cd examples/csharp
dotnet run
```

## Endpoints

- `GET /page` returns a Hello World page payload and a token.
- `POST /api/hello` validates the token and returns `Hello World`.
