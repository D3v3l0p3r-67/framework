<?php
require_once('./core/Database.php');
require_once('./core/Session.php');

use Framework\Core\Database;
use Framework\Core\Session;

class Authenticator
{
    //Only check if session has access token and it is not expired!
    public static function isAuthenticated(): bool
    {
        $session = new Session();
        $session_logged_in = $session->getLoggedIn();

        //check if user has valid session
        if ($session_logged_in) {
            return true;
        }

        return false;
    }

    public static function isAuthorized($actionKey)
    {
        $username = (new Session())->getUsername();
        $authorized =  (new Database())->row(
            'SELECT fw_authorization.action_key
             FROM fw_user
             JOIN fw_user_role on fw_user_role.user_id = fw_user.id
             JOIN fw_authorization on fw_authorization.role_id = fw_user_role.role_id
             WHERE fw_user.username = ? and
                   fw_authorization.action_key = ?',
            [$username, $actionKey]
        );

        if ($authorized) {
            return true;
        }

        return false;
    }
}
