<?php
require_once('./core/ResponseFactory.php');
require_once('./core/Response.php');
require_once('./core/Authenticator.php');
require_once('./actions/User.php');

class Action
{
    public static function Execute($accessToken, $actionKey, $actionParameters)
    {
        if (substr_count($actionKey, '.') !== 1) {
            throw new Exception("Action key '$actionKey' is not in correct format.");
            exit();
        }

        $actionKeyArray = explode('.', $actionKey);
        $className = $actionKeyArray[0];
        $methodName = $actionKeyArray[1];

        $fileName = './actions/' . $className . '.php';

        if (!file_exists($fileName)) {
            throw new Exception("Class file '$fileName' does not exists.");
            exit();
        }

        require_once($fileName);

        if (!class_exists($className)) {
            throw new Exception("Class '$className' does not exists.");
            exit();
        }

        $instance = new $className;

        if (!method_exists($instance, $methodName)) {
            throw new Exception("Method '$methodName' does not exists in class '$className'.");
            exit();
        }

        if ( self::IsLoginActionKey($actionKey) || User::IsAdministrator() || Authenticator::isAuthorized($actionKey)) {
            return call_user_func_array(array($instance, $methodName), $actionParameters);
        } else {
            return self::GetLoginFormResponse();
            //ResponseFactory::CreateForbiden()->send();
            //exit();
        }
    }
    private static function IsLoginActionKey($actionKey)
    {
        return "User.Login" == $actionKey;
    }


    private static function GetLoginFormResponse()
    {
        $name = 'User.LoginForm';

        return ResponseFactory::CreateOk(
            message: new MessageLog('Records retrieved from the database'),
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

    /*
    private static function CanCallFunction($accessToken, $actionKey)
    {
        if (self::CanExecuteWithoutAuthentication($actionKey) || Authenticator::isAuthenticated()) {
            return true;
        } else {
            ResponseFactory::CreateForbiden()->send();
            exit();
        }
    }

    private static function CanExecuteWithoutAuthentication($actionKey)
    {
        $allowedMethods = ['Get', 'List', 'Login'];

        foreach ($allowedMethods as $allowedMethod) {
            $allowedMethod = '.' . $allowedMethod;
            if (substr($actionKey, -strlen($allowedMethod)) === $allowedMethod) {
                return true;
            }
        }
        return false;
    }
    */
}
