# PHP Example

This example shows token generation, API invocation, validation, and replay detection with the PHP reference library.

```php
<?php
require_once __DIR__ . '/../../src/autoload.php';

use Otpap\Reference\Crypto;
use Otpap\Reference\InMemoryReplayStore;
use Otpap\Reference\OTPAPEngine;
use Otpap\Reference\OTPAPGenerator;
use Otpap\Reference\OTPAPValidator;
use Otpap\Reference\RequestContext;
use Otpap\Reference\SessionManager;

// Server setup.
$crypto = new Crypto();
$sessions = new SessionManager();
$replay = new InMemoryReplayStore();
$generator = new OTPAPGenerator($crypto, $sessions);
$validator = new OTPAPValidator($crypto, $sessions, $replay);
$engine = new OTPAPEngine($generator, $validator);
$secret = 'replace-with-a-strong-secret';

// Login has already happened, so create and bind the session.
$session = $sessions->createSession('warehouse', 'user_42', 3600);
$sessions->bindPage($session->getSessionId(), 'page_orders');

// Build the protected request context.
$request = new RequestContext(
    'warehouse',
    $session->getSessionId(),
    'user_42',
    'page_orders',
    'orders.create',
    'POST',
    '{"orderId":123,"amount":45.50}'
);

// Generate and attach the OTPAP token.
$token = $engine->generateToken($request, $secret, 60);

// Validate before executing business logic.
$result = $engine->validateToken($token, $request, $secret, time());
if (!$result->isValid()) {
    echo $result->getCode();
    exit(1);
}

// Replay detection: this second validation is rejected.
$replayResult = $engine->validateToken($token, $request, $secret, time());
if (!$replayResult->isValid()) {
    echo $replayResult->getCode();
}
```
