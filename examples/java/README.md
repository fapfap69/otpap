# Java Hello World Example

This example uses Spring Boot to expose a Hello World API protected by OTPAP.

## Files

- `pom.xml`
- `src/main/java/org/otpap/example/HelloWorldApplication.java`
- `src/main/java/org/otpap/example/OtpapCrypto.java`
- `src/main/java/org/otpap/example/OtpapValidator.java`

## Run

```bash
cd examples/java
mvn spring-boot:run
```

## Endpoints

- `GET /page` returns a Hello World page payload and a token.
- `POST /api/hello` validates the token and returns `Hello World`.
