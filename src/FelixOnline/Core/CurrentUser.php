<?php
namespace FelixOnline\Core;
/*
 * Current User class
 */
class CurrentUser {
    function __construct() {
        $app = App::getInstance();

        if(!isset($app['env']['session'])) {
            $app['env']['session'] = new Session(SESSION_NAME);
        }
        $app['env']['session']->start();

        if(!isset($app['env']['cookies'])) {
            $app['env']['cookies'] = new Cookies();
        }

        if(
            $this->isLoggedIn()
            && $app['env']['session']->session['uname'] != NULL
        ) {
            $this->setUser($app['env']['session']->session['uname']);
        }
    }
    public function isLoggedIn() {}
    public function setUser($username) {}
    public function createSession() {}
    public function getSession() {}
    protected function validateSession() {}
    public function stashSession() {}
    public function restoreSession($existing_id) {}
    public function resetSession($flushdb = true) {}
    private function destroySessions() {}
    private function destroyOldSessions() {}
    public function removeCookie() {}
    public function setCookie() {}
    protected function loginFromCookie() {}
}
