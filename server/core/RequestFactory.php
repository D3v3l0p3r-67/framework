<?php
require_once('./core/Request.php');

class RequestFactory
{

    public static function Create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return self::CreateFromGet();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return self::CreateFromPost();
        } else {
            throw new Exception("Unsupported request method.");
        }
    }

    public static function CreateFromPost()
    {
        $request = json_decode($_POST['request'] ?? '', true);

        $accessToken = null; //$request['accessToken'];
        $actionKey = $request['actionKey'] ?? '';
        $actionParameters = $request['actionParameters'] ?? array(array());

        if (!is_array($actionParameters) || count($actionParameters) !== 1 || !is_array($actionParameters[0])) {
            throw new Exception("Action parameters are not in the proper format. It must be an array of object/s or null.");
            exit();
        }

        return new Request($accessToken, $actionKey, $actionParameters);
    }

    public static function CreateFromGet()
    {
        $accessToken = null; //$_GET['accessToken'];
        $actionKey = $_GET['actionKey'] ?? '';

        // Collect all parameters except accessToken and actionKey
        $actionParameters = [];

        foreach ($_GET as $paramName => $paramValue) {
            if ($paramName !== 'accessToken' && $paramName !== 'actionKey') {
                $actionParameters[$paramName] = $paramValue;
            }
        }

        return new Request($accessToken, $actionKey, [$actionParameters]);
    }
}
