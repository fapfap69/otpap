# JavaScript Browser Example

This browser example shows how a page can carry a one-time token to a protected API call.

```javascript
// The page receives `otpapToken` from the server during HTML rendering.
const otpapToken = window.__OTPAPToken;

async function createOrder() {
  // The request body is bound into the token, so the body must match exactly.
  const body = JSON.stringify({ orderId: 123, amount: 45.50 });

  const response = await fetch('/api/orders/create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-OTPAP-Token': JSON.stringify(otpapToken)
    },
    body
  });

  if (!response.ok) {
    throw new Error(`Request rejected: ${response.status}`);
  }

  return response.json();
}

createOrder().then(console.log);
```

Server-side validation SHOULD compare the token, session, page, endpoint, method, body hash, signature, and replay state before executing the API handler.
