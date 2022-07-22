<?php

namespace life2016\phpredis\components;


use life2016\phpredis\models\CommonUtil;
use yii\base\Model;
use yii\helpers\Url;

class Login extends Model
{
    /**
     * @var $instance \life2016\phpredis\components\Configs
     */
    public $instance;

    public $username;
    public $password;

    public function rules()
    {
        return [
            [['username', 'password'], 'trim'],
            [['username', 'password'], 'required'],
        ];
    }

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function checkLogin()
    {
        //自带网页登录
        if ($this->instance->cookie_auth) {
            return $this->authCookie();
        } else {
            return $this->authHttpDigest();
        }
    }

    /**
     * This fill will perform HTTP digest authentication. This is not the most secure form of authentication so be carefull when using this.
     * @return mixed
     */
    public function authHttpDigest()
    {
        
        $realm = 'phpRedisAdmin';

        // Using the md5 of the user agent and IP should make it a bit harder to intercept and reuse the responses.
        $opaque = md5('phpRedisAdmin' . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

        if (!isset($_SERVER['PHP_AUTH_DIGEST']) || empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . $opaque . '"');
            die;
        }

        $needed_parts = array(
            'nonce'    => 1,
            'nc'       => 1,
            'cnonce'   => 1,
            'qop'      => 1,
            'username' => 1,
            'uri'      => 1,
            'response' => 1
        );

        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('/(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))/', $_SERVER['PHP_AUTH_DIGEST'], $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        if (!empty($needed_parts)) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . $opaque . '"');
            die;
        }

        if (!isset($this->instance->login[$data['username']])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . $opaque . '"');
            die('Invalid username and/or password combination.');
        }

        $login         = $this->instance->login[$data['username']];
        $login['name'] = $data['username'];

        $password = md5($login['name'] . ':' . $realm . ':' . $login['password']);

        $response = md5($password . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']));

        if ($data['response'] !== $response) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . $opaque . '"');
            die('Invalid username and/or password combination.');
        }

        return $login;
    }

    /**
     * Perform auth using a standard HTML <form> submission and cookies to save login state
     * @return |null
     */
    public function authCookie()
    {
        if (!empty($_COOKIE['phpRedisAdminLogin'])) {
            // We have a cookie; is it correct?
            // Cookie value looks like "username:password-hash"
            $cookieVal = explode(':', $_COOKIE['phpRedisAdminLogin']);
            if (count($cookieVal) === 2) {
                list($username, $cookieHash) = $cookieVal;
                if (isset($this->instance->login[$username])) {
                    $userData     = $this->instance->login[$username];
                    $expectedHash = $this->generateCookieHash($username);
                    if ($cookieHash === $expectedHash) {
                        // Correct username & password
                        $userData['name'] = $username;
                        return $userData;
                    }
                }
            }
        }

        return null;
    }

    private function generateCookieHash($username)
    {
        if (!isset($this->instance->login[$username])) {
            throw new \Exception("Invalid username");
        }

        // Storing this value client-side so we need to be careful that it
        //  doesn't reveal anything nor can be guessed.
        // Using SHA512 because MD5, SHA1 are both now considered broken
        return hash(
            'sha512',
            implode(':', [
                $username,
                $_SERVER['HTTP_USER_AGENT'],
                $_SERVER['REMOTE_ADDR'],
                $this->instance->login[$username]['password'],
            ])
        );
    }

    public function loginIn()
    {
        if (!$this->validate()) {
            return false;
        }
        //无需登录
        if (!isset($this->instance->login)) {
            return true;
        }

        if (!isset($this->instance->login[$this->username])) {
            $this->addError('username', 'Username invalid');
            return false;
        }
        //密码比较
        if ($this->instance->login[$this->username]['password'] !== $this->password) {
            $this->addError('password', 'Password invalid');
            return false;

        }
        // Correct username & password. Set cookie and redirect to home page
        $cookieValue = $this->username . ':' . $this->generateCookieHash($this->username);

        setcookie('phpRedisAdminLogin', $cookieValue, 0 , '/');

        return true;
    }
}