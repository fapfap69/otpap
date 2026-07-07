# JavaScript Browser Hello World Example

This example shows how a browser page can use an OTPAP token with a protected API call.

## Files

- `index.html`
- `app.js`

## Run

Serve the `examples/javascript` directory with any static HTTP server and open `index.html` in a browser.

## Flow

- The page fetches a token from `/page`.
- The page sends the token to `/api/hello`.
- The API validates, consumes, and rejects replays.
