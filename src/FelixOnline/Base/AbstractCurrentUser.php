<?php
namespace FelixOnline\Base;

/*
 * Current User class
 */
abstract class AbstractCurrentUser
{
    private $user = null;

    public function __construct()
    {
        $app = App::getInstance();

        if (
            $app->getMode() == App::MODE_HTTP ||
            $app->isRunningUnitTests()
        ) {
            $app['env']['session']->start();

            if ($this->isLoggedIn() && $app['env']['session']['uname'] != null) {
                $this->setUser($app['env']['session']['uname']);
            }
        }
    }

    public function isLoggedIn()
    {
        $app = App::getInstance();

        if (isset($app['env']['session']['loggedin']) && $app['env']['session']['loggedin']) {
            return $this->logInFromSession();
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    protected function setUser($username)
    {
        $this->user = $username;
    }

    protected function unsetUser()
    {
        $this->user = null;
    }

    abstract public function logIn(AbstractUser $user);
    abstract public function logInFromSession();
    abstract public function createSession();

    abstract public function logOut();
    abstract public function destroySession();
}
