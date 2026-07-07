package org.otpap.example;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.SecureRandom;
import java.util.HexFormat;
import java.util.Map;
import java.util.TreeMap;

/** Utility methods for the Java Hello World example. */
public final class OtpapCrypto {
    private static final SecureRandom RANDOM = new SecureRandom();

    private OtpapCrypto() {
    }

    /** Returns a SHA-256 hex digest for the provided body. */
    public static String hashBody(String body) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            return HexFormat.of().formatHex(digest.digest(body.getBytes(StandardCharsets.UTF_8)));
        } catch (Exception exception) {
            throw new IllegalStateException("Unable to hash body.", exception);
        }
    }

    /** Returns an HMAC-SHA256 hex signature. */
    public static String sign(Map<String, Object> token, String secret) {
        try {
            Mac mac = Mac.getInstance("HmacSHA256");
            mac.init(new SecretKeySpec(secret.getBytes(StandardCharsets.UTF_8), "HmacSHA256"));
            mac.update(canonicalJson(withoutSignature(token)).getBytes(StandardCharsets.UTF_8));
            return HexFormat.of().formatHex(mac.doFinal());
        } catch (Exception exception) {
            throw new IllegalStateException("Unable to sign token.", exception);
        }
    }

    /** Returns a canonical JSON string with deterministic key ordering. */
    public static String canonicalJson(Map<String, Object> value) {
        StringBuilder builder = new StringBuilder("{");
        boolean first = true;
        for (Map.Entry<String, Object> entry : new TreeMap<>(value).entrySet()) {
            if (!first) {
                builder.append(',');
            }
            builder.append('"').append(entry.getKey()).append('"').append(':').append(jsonValue(entry.getValue()));
            first = false;
        }
        builder.append('}');
        return builder.toString();
    }

    /** Returns a lowercase hex nonce. */
    public static String randomNonce(int bytes) {
        byte[] buffer = new byte[bytes];
        RANDOM.nextBytes(buffer);
        return HexFormat.of().formatHex(buffer);
    }

    private static String jsonValue(Object value) {
        if (value instanceof String stringValue) {
            return '"' + stringValue.replace("\"", "\\\"") + '"';
        }
        return String.valueOf(value);
    }

    private static Map<String, Object> withoutSignature(Map<String, Object> token) {
        Map<String, Object> payload = new TreeMap<>(token);
        payload.remove("Signature");
        return payload;
    }
}
