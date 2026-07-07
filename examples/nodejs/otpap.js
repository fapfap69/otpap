/**
 * OTPAP helper utilities for the Node.js Hello World example.
 */

const crypto = require('crypto');

function canonicalJson(value) {
  if (Array.isArray(value)) {
    return value.map(canonicalizeValue);
  }
  if (value && typeof value === 'object') {
    return Object.keys(value)
      .sort()
      .reduce((accumulator, key) => {
        accumulator[key] = canonicalizeValue(value[key]);
        return accumulator;
      }, {});
  }
  return value;
}

function canonicalizeValue(value) {
  return Array.isArray(value) || (value && typeof value === 'object') ? canonicalJson(value) : value;
}

function canonicalStringify(value) {
  return JSON.stringify(canonicalJson(value));
}

function hashBody(body) {
  return crypto.createHash('sha256').update(body, 'utf8').digest('hex');
}

function signToken(token, secret) {
  const payload = { ...token };
  delete payload.Signature;
  return crypto.createHmac('sha256', secret).update(canonicalStringify(payload), 'utf8').digest('hex');
}

function createToken(context, secret, ttlSeconds = 60) {
  const timestamp = Math.floor(Date.now() / 1000);
  const token = {
    ProtocolVersion: '2.0',
    ApplicationId: context.applicationId,
    SessionId: context.sessionId,
    UserId: context.userId,
    PageId: context.pageId,
    ApiId: context.apiId,
    HttpMethod: context.httpMethod,
    Nonce: crypto.randomBytes(8).toString('hex'),
    SequenceNumber: context.sequenceNumber,
    Timestamp: timestamp,
    Expiration: timestamp + ttlSeconds,
    BodyHash: hashBody(context.body),
    Signature: ''
  };

  token.Signature = signToken(token, secret);
  return token;
}

function validateToken(token, context, secret, replayStore) {
  if (!token || token.ProtocolVersion !== '2.0') {
    return { valid: false, code: 'OTPAP-1001', message: 'Unsupported token format.' };
  }

  if (token.ApplicationId !== context.applicationId || token.SessionId !== context.sessionId || token.UserId !== context.userId) {
    return { valid: false, code: 'OTPAP-1005', message: 'Session binding failed.' };
  }

  if (token.PageId !== context.pageId) {
    return { valid: false, code: 'OTPAP-1006', message: 'Page binding failed.' };
  }

  if (token.ApiId !== context.apiId) {
    return { valid: false, code: 'OTPAP-1007', message: 'API binding failed.' };
  }

  if (token.HttpMethod !== context.httpMethod.toUpperCase()) {
    return { valid: false, code: 'OTPAP-1008', message: 'HTTP method binding failed.' };
  }

  const now = Math.floor(Date.now() / 1000);
  if (token.Timestamp > now + 5 || token.Expiration < now) {
    return { valid: false, code: 'OTPAP-1004', message: 'Token is expired.' };
  }

  const bodyHash = hashBody(context.body);
  if (token.BodyHash !== bodyHash) {
    return { valid: false, code: 'OTPAP-1009', message: 'Body hash mismatch.' };
  }

  const expectedSignature = signToken(token, secret);
  if (!crypto.timingSafeEqual(Buffer.from(token.Signature, 'hex'), Buffer.from(expectedSignature, 'hex'))) {
    return { valid: false, code: 'OTPAP-1003', message: 'Signature verification failed.' };
  }

  const tokenId = crypto.createHash('sha256').update(canonicalStringify({ ...token, Signature: undefined })).digest('hex');
  if (replayStore.has(tokenId)) {
    return { valid: false, code: 'OTPAP-1010', message: 'Replay detected.' };
  }

  replayStore.add(tokenId);
  return { valid: true, code: 'OTPAP-0000', message: 'Token validated and consumed.', tokenId };
}

module.exports = {
  createToken,
  validateToken,
};
