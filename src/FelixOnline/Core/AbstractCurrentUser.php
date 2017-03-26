<?php
namespace FelixOnline\Core;
/*
 * Current User class
 */
abstract class AbstractCurrentUser {
    private $user = null;

    public function __construct() {
        $app = App::getInstance();

        if($app->getMode() == App::MODE_HTTP) {
            if(!isset($app['env']['session'])) {
                $app['env']['session'] = new Session(SESSION_NAME);
            }
        }
    }

    public function isLoggedIn() {
        return is_null($this->user);
    }

    public function getUser() {
        return $this->user;
    }

    protected function setUser(AbstractUser $username) {
        $this->user = $username;
    }

    protected function unsetUser() {
        $this->user = null;
    }

    abstract public function logIn(AbstractUser $user);
    abstract public function logInFromSession(AbstractUser $user);
    abstract public function logInFromCookie(AbstractUser $user);

    abstract public function logOut();
    abstract public function logOutFromSession();
    abstract public function logOutFromCookie();
}
