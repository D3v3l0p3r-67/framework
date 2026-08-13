<?php

chdir(__DIR__ . '/../server');

require_once './core/Action.php';

$login = new ReflectionMethod(User::class, 'Login');
if ($login->getAttributes(Action::class) === []) {
    throw new RuntimeException('User.Login is not exposed with #[Action].');
}

$administratorHelper = new ReflectionMethod(User::class, 'IsAdministrator');
if ($administratorHelper->getAttributes(Action::class) !== []) {
    throw new RuntimeException('User.IsAdministrator must not be exposed as an action.');
}

try {
    Action::Execute(null, 'User.IsAdministrator', [[]]);
    throw new RuntimeException('Unattributed public method was executed.');
} catch (HttpException $exception) {
    if ($exception->getStatusCode() !== 404) {
        throw $exception;
    }
}

$loginFormFactory = new ReflectionMethod(Action::class, 'GetLoginFormResponse');
$loginResponse = $loginFormFactory->invoke(null)->jsonSerialize();
if ($loginResponse['statusCode'] !== 401 || $loginResponse['success'] !== false) {
    throw new RuntimeException('Authentication challenge must use an HTTP 401 error response.');
}
if (!is_array($loginResponse['form']) || !str_contains($loginResponse['form']['template'] ?? '', 'User.Login')) {
    throw new RuntimeException('Authentication challenge did not preserve the login form.');
}

echo "Action attribute tests passed.\n";
