# PHP Hello World Example

This is the functional OTPAP reference example for PHP.

## Files

- `../../src/` contains the protocol engine.
- `reference.php` is shown inline below as the integration pattern.

## Run

Use the reference classes from `src/` exactly as shown in the snippet.

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

$crypto = new Crypto();
$sessions = new SessionManager();
$replay = new InMemoryReplayStore();
$engine = new OTPAPEngine(
    new OTPAPGenerator($crypto, $sessions),
    new OTPAPValidator($crypto, $sessions, $replay)
);

$session = $sessions->createSession('hello-world-app', 'user_hello', 3600);
$sessions->bindPage($session->getSessionId(), 'page_hello');

$request = new RequestContext(
    'hello-world-app',
    $session->getSessionId(),
    'user_hello',
    'page_hello',
    'hello.world',
    'POST',
    '{"message":"Hello World"}'
);

$secret = 'php-hello-world-secret';
$token = $engine->generateToken($request, $secret, 60);
$result = $engine->validateToken($token, $request, $secret, time());

echo json_encode($result->toArray(), JSON_PRETTY_PRINT), PHP_EOL;
```
