<?php

namespace Framework\Core;

class Session
{
    protected $user_id_key = 'user_id';
    protected $username_key = 'username';
    protected $email_key = 'email';
    protected $logged_in_key = 'logged_in';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public function getLoggedIn()
    {
        $logged_in = $this->get($this->logged_in_key);

        if (!isset($logged_in)) {
            $logged_in = false;
        }

        return $logged_in;
    }
    public function setLoggedIn($logged_in)
    {
        $this->set($this->logged_in_key, $logged_in);
    }

    public function getUsername()
    {
        $username = $this->get($this->username_key);

        if (!isset($username)) {
            $username = 'Guest';
        }

        return $username;
    }
    public function setUsername($username)
    {
        $this->set($this->username_key, $username);
    }

    public function getEmail()
    {
        $email = $this->get($this->email_key);

        if (!isset($email)) {
            $email = '';
        }

        return $email;
    }
    public function setEmail($email)
    {
        $this->set($this->email_key, $email);
    }

    public function getUserId()
    {
        $user_id = $this->get($this->user_id_key);

        if (!isset($user_id)) {
            $user_id = 2;
        }

        return $user_id;
    }
    public function setUserId($userId)
    {
        $this->set($this->user_id_key, $userId);
    }

    public function get(string $key)
    {
        if ($this->has($key)) {
            return $_SESSION[$key];
        }

        return null;
    }

    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function clear(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public function regenerateId(): void
    {
        session_regenerate_id(true);
    }

    public function getCsrfToken(): string
    {
        $token = $this->get('csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
        }

        return $token;
    }

    public function isValidCsrfToken(?string $token): bool
    {
        return is_string($token) && hash_equals($this->getCsrfToken(), $token);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public function print()
    {
        echo "Session vars: <br>";

        foreach ($_SESSION as $key => $value) {
            echo $key . " = " . $value . "<br>";
        }

        echo "-----------------<br>";
    }
}
