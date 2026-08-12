<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('./core/Request.php');
require_once('./core/RequestFactory.php');
require_once('./core/Response.php');
require_once('./core/ResponseFactory.php');
require_once('./core/Action.php');
require_once('./core/Logger.php');


try {
    $request = RequestFactory::Create();
    $response = $request->Execute();

    if ($response) {
        $response->SetRequest($request->asArray());
        $response->Send();
    }
} catch (Throwable $ex) {
    ResponseFactory::CreateError(
        code: 500,
        messages: new MessageArray([
            new MessageError($ex->getMessage())
        ])
        //messages: [array($ex->__toString())] //for call trace, not on production!
        //messages: [array('Fatal error')] //constant message for production
    )->send();
}
