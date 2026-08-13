<?php

chdir(__DIR__);

$localConfig = file_exists('./config.local.php') ? require './config.local.php' : [];
$debug = filter_var(
    $localConfig['app']['debug'] ?? (getenv('APP_DEBUG') ?: false),
    FILTER_VALIDATE_BOOL
);

ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

require_once('./core/Request.php');
require_once('./core/RequestFactory.php');
require_once('./core/Response.php');
require_once('./core/ResponseFactory.php');
require_once('./core/Action.php');
require_once('./core/Logger.php');
require_once('./core/HttpException.php');


try {
    $request = RequestFactory::Create();

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        $request->getActionKey() !== 'User.Login' &&
        Authenticator::isAuthenticated()
    ) {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!(new Framework\Core\Session())->isValidCsrfToken($csrfToken)) {
            throw new HttpException('Invalid CSRF token.', 403);
        }
    }

    $response = $request->Execute();

    if ($response) {
        $response->SetRequest($request->asArray());
        $response->Send();
    }
} catch (Throwable $ex) {
    $statusCode = $ex instanceof HttpException ? $ex->getStatusCode() : 500;
    ResponseFactory::CreateError(
        code: $statusCode,
        messages: new MessageArray([
            new MessageError($debug || $ex instanceof HttpException ? $ex->getMessage() : 'Internal server error')
        ])
        //messages: [array($ex->__toString())] //for call trace, not on production!
        //messages: [array('Fatal error')] //constant message for production
    )->send();
}
