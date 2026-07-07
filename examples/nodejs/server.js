/**
 * Node.js Express Hello World API protected by OTPAP.
 */

const express = require('express');
const { createToken, validateToken } = require('./otpap');

const app = express();
app.use(express.json());

const secret = 'nodejs-hello-world-secret';
const replayStore = new Set();
const session = {
  applicationId: 'hello-world-app',
  sessionId: 'sess_node_001',
  userId: 'user_hello',
  pageId: 'page_hello',
  apiId: 'hello.world',
  httpMethod: 'POST',
  sequenceNumber: 1
};

app.get('/page', (_req, res) => {
  const token = createToken({ ...session, body: JSON.stringify({ message: 'Hello World' }) }, secret, 60);
  res.json({
    page: 'Hello World',
    token,
    note: 'Send the token to POST /api/hello with the same body.'
  });
});

app.post('/api/hello', (req, res) => {
  const tokenHeader = req.header('X-OTPAP-Token');
  if (!tokenHeader) {
    return res.status(400).json({ valid: false, code: 'OTPAP-1001', message: 'Missing token.' });
  }

  const token = JSON.parse(tokenHeader);
  const result = validateToken(token, { ...session, body: JSON.stringify(req.body) }, secret, replayStore);
  if (!result.valid) {
    return res.status(409).json(result);
  }

  return res.json({
    valid: true,
    code: result.code,
    message: 'Hello World',
    consumed: true
  });
});

app.listen(3000, () => {
  console.log('Node.js Hello World example listening on http://localhost:3000');
});
