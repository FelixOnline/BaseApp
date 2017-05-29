<?php
namespace FelixOnline\Base;

/*
 * Current User class
 */
class StubCurrentUser extends AbstractCurrentUser
{
    public function __construct()
    {
        $app = App::getInstance();

        if (!$app->isRunningUnitTests()) {
            trigger_error('Stub CurrentUser is in use - this is probably not what you want.', E_USER_WARNING);
        }

        return parent::__construct();
    }

    public function logIn(AbstractUser $user)
    {
        $this->user = $user;
    }

    public function logInFromSession()
    {
    }

    public function createSession()
    {
    }

    public function logOut()
    {
        $this->user = null;
    }

    public function destroySession()
    {
    }
}
