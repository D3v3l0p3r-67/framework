<?php

chdir(__DIR__ . '/../server');

require_once './core/RequestFactory.php';

function expectRequestStatus(callable $callback, int $status): void
{
    try {
        $callback();
    } catch (HttpException $exception) {
        if ($exception->getStatusCode() === $status) {
            return;
        }
        throw $exception;
    }

    throw new RuntimeException("Expected request status $status was not thrown.");
}

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['request'] = '{invalid';
expectRequestStatus(fn () => RequestFactory::Create(), 400);

$_POST['request'] = json_encode(['actionKey' => '../Unsafe.Run', 'actionParameters' => [[]]]);
expectRequestStatus(fn () => RequestFactory::Create(), 400);

$_POST['request'] = json_encode(['actionKey' => 'User.Login', 'actionParameters' => [[]]]);
$request = RequestFactory::Create();
if ($request->getActionKey() !== 'User.Login') {
    throw new RuntimeException('Valid request was not created.');
}

$_POST['request'] = json_encode([
    'actionKey' => 'User.Login',
    'actionParameters' => [['username' => 'user', 'password' => 'secret']],
]);
$serialized = RequestFactory::Create()->asArray();
if ($serialized['actionParameters'][0]['password'] !== '[REDACTED]') {
    throw new RuntimeException('Sensitive request values were not redacted.');
}

echo "RequestFactory tests passed.\n";
