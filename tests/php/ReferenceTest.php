<?php
/**
 * Self-contained PHP reference tests for OTPAP.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../src/autoload.php';

use Otpap\Reference\ApiDispatcher;
use Otpap\Reference\Crypto;
use Otpap\Reference\InMemoryReplayStore;
use Otpap\Reference\OTPAPEngine;
use Otpap\Reference\OTPAPGenerator;
use Otpap\Reference\OTPAPValidator;
use Otpap\Reference\RequestContext;
use Otpap\Reference\SessionManager;

/**
 * Fails the test run with a clear message.
 */
function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$crypto = new Crypto();
$sessions = new SessionManager();
$replayStore = new InMemoryReplayStore();
$generator = new OTPAPGenerator($crypto, $sessions);
$validator = new OTPAPValidator($crypto, $sessions, $replayStore);
$engine = new OTPAPEngine($generator, $validator);
$dispatcher = new ApiDispatcher($engine);
$secret = 'test-secret-key';

$session = $sessions->createSession('warehouse', 'user_42', 3600);
$sessions->bindPage($session->getSessionId(), 'page_orders');

$request = new RequestContext(
    'warehouse',
    $session->getSessionId(),
    'user_42',
    'page_orders',
    'orders.create',
    'POST',
    '{"orderId":123,"amount":45.50}'
);

$token = $engine->generateToken($request, $secret, 60);
$first = $engine->validateToken($token, $request, $secret, $token->timestamp);
if (!$first->isValid()) {
    fail('Expected the reference token to validate.');
}

$freshToken = $engine->generateToken($request, $secret, 60);
$tamperedRequest = new RequestContext(
    'warehouse',
    $session->getSessionId(),
    'user_42',
    'page_orders',
    'orders.create',
    'POST',
    '{"orderId":123,"amount":999.99}'
);

$tamperedResult = $engine->validateToken($freshToken, $tamperedRequest, $secret, $freshToken->timestamp);
if ($tamperedResult->isValid() || $tamperedResult->getCode() !== 'OTPAP-1009') {
    fail('Expected body tampering to be rejected.');
}

$second = $engine->validateToken($token, $request, $secret, $token->timestamp);
if ($second->isValid() || $second->getCode() !== 'OTPAP-1010') {
    fail('Expected replay detection on second validation.');
}

$dispatchResult = $dispatcher->dispatch(
    $request,
    $engine->generateToken($request, $secret, 60),
    $secret,
    static function (RequestContext $context, \Otpap\Reference\OtpapToken $token): array {
        return [
            'apiId' => $context->apiId,
            'sequenceNumber' => $token->sequenceNumber,
        ];
    },
    time()
);

if (!$dispatchResult['valid'] || !isset($dispatchResult['data']['apiId'])) {
    fail('Expected dispatcher success path to return data.');
}

echo "OTPAP reference tests passed." . PHP_EOL;
