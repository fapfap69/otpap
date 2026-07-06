# NodeJS Express Example

```javascript
const express = require('express');
const app = express();
app.use(express.json());

// A production app would use a shared OTPAP library or service module.
const replayStore = new Map();

function validateOtpap(req, res, next) {
  const token = JSON.parse(req.header('X-OTPAP-Token'));
  const tokenId = `${token.ApplicationId}:${token.SessionId}:${token.PageId}:${token.ApiId}:${token.HttpMethod}:${token.Nonce}:${token.SequenceNumber}`;

  // Replay detection.
  if (replayStore.has(tokenId)) {
    return res.status(409).json({ valid: false, code: 'OTPAP-1010' });
  }

  // Validate request context and body hash here.
  replayStore.set(tokenId, true);
  next();
}

app.post('/api/orders/create', validateOtpap, (req, res) => {
  // Business execution happens only after OTPAP validation.
  res.json({ valid: true, code: 'OTPAP-0000', created: true });
});

app.listen(3000);
```
