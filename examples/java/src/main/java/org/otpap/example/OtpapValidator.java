package org.otpap.example;

import java.util.Map;
import java.util.Set;

/** Validates OTPAP tokens for the Java Hello World example. */
public final class OtpapValidator {
    /** Validates the request and returns a protocol response map. */
    public Map<String, Object> validate(Map<String, Object> token, Map<String, Object> context, String secret, Set<String> replayStore) {
        if (!"2.0".equals(token.get("ProtocolVersion"))) {
            return Map.of("valid", false, "code", "OTPAP-1001", "message", "Unsupported token format.");
        }
        if (!stringValue(token.get("ApplicationId")).equals(stringValue(context.get("applicationId")))) {
            return Map.of("valid", false, "code", "OTPAP-1005", "message", "Session binding failed.");
        }
        if (!stringValue(token.get("PageId")).equals(stringValue(context.get("pageId")))) {
            return Map.of("valid", false, "code", "OTPAP-1006", "message", "Page binding failed.");
        }
        if (!stringValue(token.get("ApiId")).equals(stringValue(context.get("apiId")))) {
            return Map.of("valid", false, "code", "OTPAP-1007", "message", "API binding failed.");
        }
        if (!stringValue(token.get("HttpMethod")).equals(stringValue(context.get("httpMethod")).toUpperCase())) {
            return Map.of("valid", false, "code", "OTPAP-1008", "message", "Method binding failed.");
        }
        long expiration = Long.parseLong(String.valueOf(token.get("Expiration")));
        if (expiration < java.time.Instant.now().getEpochSecond()) {
            return Map.of("valid", false, "code", "OTPAP-1004", "message", "Token expired.");
        }
        if (!token.get("BodyHash").equals(OtpapCrypto.hashBody((String) context.get("body")))) {
            return Map.of("valid", false, "code", "OTPAP-1009", "message", "Body hash mismatch.");
        }
        if (!token.get("Signature").equals(OtpapCrypto.sign(token, secret))) {
            return Map.of("valid", false, "code", "OTPAP-1003", "message", "Signature verification failed.");
        }
        String tokenId = OtpapCrypto.hashBody(OtpapCrypto.canonicalJson(token));
        if (replayStore.contains(tokenId)) {
            return Map.of("valid", false, "code", "OTPAP-1010", "message", "Replay detected.");
        }
        replayStore.add(tokenId);
        return Map.of("valid", true, "code", "OTPAP-0000", "message", "Token validated and consumed.", "tokenId", tokenId);
    }

    private static String stringValue(Object value) {
        return value == null ? "" : String.valueOf(value);
    }
}
