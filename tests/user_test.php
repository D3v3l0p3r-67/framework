<?php

chdir(__DIR__ . '/../server');

require_once './core/Action.php';

use Framework\Core\Session;

$session = new Session();
$session->setLoggedIn(true);
$session->setUserId(123);
$session->setUsername('test-user');

$response = (new User())->Login([])->jsonSerialize();
if ($response['statusCode'] !== 409 || $response['success'] !== false) {
    throw new RuntimeException('Logging in twice must return an HTTP 409 conflict.');
}

$session->clear();

echo "User status tests passed.\n";
