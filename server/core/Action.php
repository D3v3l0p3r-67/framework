<?php
require_once('./core/ResponseFactory.php');
require_once('./core/Response.php');
require_once('./core/Authenticator.php');
require_once('./actions/User.php');
require_once('./core/HttpException.php');

use Framework\Core\Logger;
use Framework\Core\LogLevel;

#[Attribute(Attribute::TARGET_METHOD)]
class Action
{
    public static function Execute($accessToken, $actionKey, $actionParameters)
    {
        if (substr_count($actionKey, '.') !== 1) {
            throw new HttpException("Action key '$actionKey' is not in correct format.", 400);
        }

        $actionKeyArray = explode('.', $actionKey);
        $className = $actionKeyArray[0];
        $methodName = $actionKeyArray[1];

        $fileName = './actions/' . $className . '.php';

        if (!file_exists($fileName)) {
            throw new HttpException("Action '$actionKey' was not found.", 404);
        }

        require_once($fileName);

        if (!class_exists($className)) {
            throw new HttpException("Action '$actionKey' was not found.", 404);
        }

        $instance = new $className;

        if (!method_exists($instance, $methodName)) {
            throw new HttpException("Action '$actionKey' was not found.", 404);
        }

        $method = new ReflectionMethod($instance, $methodName);
        if (!$method->isPublic()) {
            throw new HttpException("Action '$actionKey' was not found.", 404);
        }

        self::AssertExposedAction($method, $actionKey);

        $authorized = self::IsLoginActionKey($actionKey) || User::IsAdministrator() || Authenticator::isAuthorized($actionKey);
        self::Audit($actionKey, $authorized);

        if ($authorized) {
            return call_user_func_array(array($instance, $methodName), $actionParameters);
        }

        return self::GetLoginFormResponse();
    }

    private static function AssertExposedAction(ReflectionMethod $method, string $actionKey): void
    {
        if ($method->getAttributes(self::class) !== []) {
            return;
        }

        $config = file_exists('./config.local.php') ? require './config.local.php' : [];
        $auditMode = filter_var(
            $config['app']['action_audit'] ?? (getenv('ACTION_AUDIT') ?: false),
            FILTER_VALIDATE_BOOL
        );

        if ($auditMode) {
            Logger::Write("Unattributed action executed in audit mode: $actionKey", LogLevel::WARNING);
            return;
        }

        throw new HttpException("Action '$actionKey' was not found.", 404);
    }
    private static function IsLoginActionKey($actionKey)
    {
        return "User.Login" == $actionKey;
    }

    private static function Audit(string $actionKey, bool $authorized): void
    {
        $username = (new Framework\Core\Session())->getUsername();
        $result = $authorized ? 'allowed' : 'denied';
        Logger::Write("Action authorization: user=$username action=$actionKey result=$result", LogLevel::INFO);
    }


    private static function GetLoginFormResponse()
    {
        $name = 'User.LoginForm';

        return ResponseFactory::CreateError(
            code: 401,
            messages: new MessageArray([new MessageError('Authentication is required.')]),
            data: array(),
            form: array(
                'source' => '',
                'title' => 'Prijava',
                'href' => '',
                'icon' => '',
                'template' =>
                '<form class="col s12" id="login">
                    <div class="row">
                        <img src="images/icons/icon-512x512.png" class="login-image" />
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="username" name="username" type="text" class="form-field">
                            <label for="username">Uporabniško ime</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="password" name="password" type="password" class="form-field">
                            <label for="password">Geslo</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <a id="login-submit" class="waves-effect waves-light btn btn-submit"
                                href="internal:User.Login?form-id=login">Prijava</a>
                        </div>
                    </div>
                </form>'
            )
        );
    }
}
