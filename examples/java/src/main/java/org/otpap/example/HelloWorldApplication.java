package org.otpap.example;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RestController;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import java.time.Instant;
import java.util.HashSet;
import java.util.LinkedHashMap;
import java.util.Map;
import java.util.Set;

/** Spring Boot Hello World API protected by OTPAP. */
@SpringBootApplication
public class HelloWorldApplication {
    /** Starts the Spring Boot application. */
    public static void main(String[] args) {
        SpringApplication.run(HelloWorldApplication.class, args);
    }

    /** Hello World controller. */
    @RestController
    static class HelloWorldController {
        private final String secret = "java-hello-world-secret";
        private final Set<String> replayStore = new HashSet<>();
        private final OtpapValidator validator = new OtpapValidator();
        private final ObjectMapper objectMapper = new ObjectMapper();

        /** Returns a page payload and token. */
        @GetMapping("/page")
        public Map<String, Object> page() {
            Map<String, Object> token = new LinkedHashMap<>();
            token.put("ProtocolVersion", "2.0");
            token.put("ApplicationId", "hello-world-app");
            token.put("SessionId", "sess_java_001");
            token.put("UserId", "user_hello");
            token.put("PageId", "page_hello");
            token.put("ApiId", "hello.world");
            token.put("HttpMethod", "POST");
            token.put("Nonce", OtpapCrypto.randomNonce(8));
            token.put("SequenceNumber", 1);
            token.put("Timestamp", Instant.now().getEpochSecond());
            token.put("Expiration", Instant.now().getEpochSecond() + 60);
            token.put("BodyHash", OtpapCrypto.hashBody("{\"message\":\"Hello World\"}"));
            token.put("Signature", "");
            token.put("Signature", OtpapCrypto.sign(token, secret));
            return Map.of("page", "Hello World", "token", token);
        }

        /** Validates the token and returns the Hello World response. */
        @PostMapping("/api/hello")
        public Map<String, Object> hello(@RequestHeader("X-OTPAP-Token") String tokenJson, @RequestBody String body) {
            Map<String, Object> token = parseToken(tokenJson);
            Map<String, Object> result = validator.validate(token, Map.of("body", body), secret, replayStore);
            if (!Boolean.TRUE.equals(result.get("valid"))) {
                return result;
            }
            return Map.of("valid", true, "code", "OTPAP-0000", "message", "Hello World", "consumed", true);
        }

        private Map<String, Object> parseToken(String tokenJson) {
            try {
                return objectMapper.readValue(tokenJson, new TypeReference<>() {});
            } catch (Exception exception) {
                throw new IllegalArgumentException("Invalid OTPAP token JSON.", exception);
            }
        }
    }
}
