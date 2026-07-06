# Java Spring Boot Example

```java
// OTPAP validation should run in a servlet filter or controller advice layer.
@RestController
public class OrdersController {
    private final Set<String> replayStore = ConcurrentHashMap.newKeySet();

    @PostMapping("/api/orders/create")
    public ResponseEntity<Map<String, Object>> createOrder(
            @RequestHeader("X-OTPAP-Token") String tokenJson,
            @RequestBody String body) {

        // Parse token JSON, verify signature, body hash, session, page, and API binding.
        String tokenId = "derived-token-id";
        if (!replayStore.add(tokenId)) {
            return ResponseEntity.status(409).body(Map.of("valid", false, "code", "OTPAP-1010"));
        }

        return ResponseEntity.ok(Map.of("valid", true, "code", "OTPAP-0000", "executed", true));
    }
}
```
