<?php
require_once('./core/Authenticator.php');
require_once('./core/Database.php');
require_once('./core/Session.php');
require_once('./core/Utils.php');

use Framework\Core\Database;
use Framework\Core\Session;

class User
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = new Database();
        $this->session = new Session();
    }

    #[Action]
    public function Logout()
    {
        $session = new Session();
        $session->clear();

        return  ResponseFactory::CreateOk(
            message: new MessageUser('You have been successfully logged out'),
            refresh: true
        );
    }

    public static function IsAdministrator()
    {
        $username = (new Session())->getUsername();
        $role = 'Administrator';

        $isAdmin =  (new Database())->row(
            'SELECT true
            FROM fw_user
            JOIN fw_user_role on fw_user_role.user_id = fw_user.id
            JOIN fw_role on fw_role.id = fw_user_role.role_id
            WHERE fw_user.username = ? and
                  fw_role.name = ? ',
            [$username, $role]
        );

        return $isAdmin;
    }

    #[Action]
    public function GetData()
    {
        /*
        $username = $this->session->getUsername();
        $data =  $this->db->row(
            'SELECT fw_authorization.action_key
            FROM fw_user
            JOIN fw_user_role on fw_user_role.user_id = fw_user.id
            JOIN fw_authorization on fw_authorization.role_id = fw_user_role.role_id
            WHERE fw_user.username = ?',
            [$username]
        );
        */

        return  ResponseFactory::CreateOk(
            message: new MessageLog('Retrieving user data was successful'),
            data: $this->GetDataFromSession()
        );
    }

    #[Action]
    public function Login($data)
    {
        if (Authenticator::isAuthenticated()) {
            return ResponseFactory::CreateConflict(
                message: new MessageError('You are already logged in.'),
                data: $this->GetDataFromSession()
            );
        } else {
            $username = $data['username'] ?? null;
            $password = $data['password'] ?? null;

            if (
                isset($username) && !empty($username) &&
                isset($password) && !empty($password)
            ) {
                $user =  $this->db->row(
                    'SELECT * 
                    FROM fw_user 
                    WHERE username = ?',
                    [$username]
                );

                if ($user && password_verify($password, $user->password)) {
                    $this->session->regenerateId();
                    $this->session->setUserId($user->id);
                    $this->session->setUsername($user->username);
                    $this->session->setEmail($user->email);
                    $this->session->setLoggedIn(true);

                    return  ResponseFactory::CreateOk(
                        message: new MessageUser('You have been successfully logged in'),
                        refresh: true
                    );
                }
            }
            return  ResponseFactory::CreateUnauthorized(
                message: new MessageError('Invalid username or password. Please check your credentials and try again.')
            );
        }
    }

    private function GetDataFromSession()
    {
        $user_id =  $this->session->getUserId();
        $username = $this->session->getUsername();
        $email = $this->session->getEmail();
        $logged_in = $this->session->getLoggedIn();

        return array(
            "user_id" => $user_id,
            "username" => $username,
            "email" => $email,
            "logged_in" => $logged_in
        );
    }

    #[Action]
    public function Profile()
    {
        $user_id =  $this->session->getUserId();

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForEdit(),
            data: array("user_id" => $user_id),
            form: Form::Get(
                title: 'Uporabniški profil',
                href: 'internal:User.Profile',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:Joke.UpdateAndShowGrid?form-id=joke-edit',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="joke-edit">
                    <div class="row">
                        <div class="input-field col s12">
                            <p>Tvoj ID: {{user_id}}</p>
                        </div>
                    </div>
                </form>'
            )
        );
    }
}
