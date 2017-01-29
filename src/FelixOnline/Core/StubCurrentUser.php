<?php
namespace FelixOnline\Core;
/*
 * Current User class
 */
class StubCurrentUser extends AbstractCurrentUser {
    public function __construct() {
        $app = App::getInstance();

        if(!$app->isUnderTest()) {
            trigger_error('WARNING: Stub CurrentUser is in use - this is probably not what you want.');
        }

        return parent::__construct();
    }

    public function logIn(AbstractUser $user) {
        $this->user = $user;
    }

    public function logInFromSession(AbstractUser $user) {
        $this->user = $user;
    }

    public function logInFromCookie(AbstractUser $user) {
        $this->user = $user;
    }

    public function logOut() {
        $this->user = null;
    }

    public function logOutFromSession() {
        $this->user = null;
    }

    public function logOutFromCookie() {
        $this->user = null;
    }
}
