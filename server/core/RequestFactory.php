<?php
require_once('./core/Request.php');
require_once('./core/HttpException.php');

class RequestFactory
{

    public static function Create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return self::CreateFromGet();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return self::CreateFromPost();
        } else {
            throw new HttpException("Unsupported request method.", 405);
        }
    }

    public static function CreateFromPost()
    {
        $rawRequest = $_POST['request'] ?? '';
        if (!is_string($rawRequest) || strlen($rawRequest) > 1048576) {
            throw new HttpException('Request payload is too large.', 413);
        }

        try {
            $request = json_decode($rawRequest, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HttpException('Request contains invalid JSON.', 400);
        }

        if (!is_array($request)) {
            throw new HttpException('Request must be a JSON object.', 400);
        }

        $accessToken = null; //$request['accessToken'];
        $actionKey = $request['actionKey'] ?? '';
        $actionParameters = $request['actionParameters'] ?? array(array());
        self::ValidateActionKey($actionKey);

        if (!is_array($actionParameters) || count($actionParameters) !== 1 || !is_array($actionParameters[0])) {
            throw new HttpException("Action parameters are not in the proper format. It must be an array containing one object.", 400);
        }

        return new Request($accessToken, $actionKey, $actionParameters);
    }

    public static function CreateFromGet()
    {
        $accessToken = null; //$_GET['accessToken'];
        $actionKey = $_GET['actionKey'] ?? '';
        self::ValidateActionKey($actionKey);

        // Collect all parameters except accessToken and actionKey
        $actionParameters = [];

        foreach ($_GET as $paramName => $paramValue) {
            if ($paramName !== 'accessToken' && $paramName !== 'actionKey') {
                $actionParameters[$paramName] = $paramValue;
            }
        }

        return new Request($accessToken, $actionKey, [$actionParameters]);
    }

    private static function ValidateActionKey(mixed $actionKey): void
    {
        if (!is_string($actionKey) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*\.[A-Za-z_][A-Za-z0-9_]*$/', $actionKey)) {
            throw new HttpException('Action key is missing or invalid.', 400);
        }
    }
}
