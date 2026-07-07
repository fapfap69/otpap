# Node.js Hello World Example

This example is a small Express API protected by OTPAP.

## Files

- `package.json`
- `server.js`
- `otpap.js`

## Run

```bash
cd examples/nodejs
npm install
npm start
```

## Endpoints

- `GET /page` returns a Hello World page payload and a token.
- `POST /api/hello` validates the token and returns `Hello World`.

The request body must match the body used when the token was generated.
