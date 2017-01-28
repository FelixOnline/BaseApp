<?php
namespace FelixOnline\Core;
/*
 * Current User class
 */
class CurrentUser extends User
{
    /*
     * Create current user object
     * Store session into object
     */
    function __construct()
    {
        parent::__construct();

        $app = App::getInstance();

        if (!isset($app['env']['session'])) {
            $app['env']['session'] = new Session(SESSION_NAME);
        }
        $app['env']['session']->start();

        if (!isset($app['env']['cookies'])) {
            $app['env']['cookies'] = new Cookies();
        }

        if ($this->isLoggedIn() && $app['env']['session']->session['uname'] != NULL) {
            $this->setUser($app['env']['session']->session['uname']);
        }
    }

    /**
     * Public: Check if user is currently logged in
     *
     * Returns boolean
     */
    public function isLoggedIn()
    {
        $app = App::getInstance();

        if (is_array($app['env']['session']->session) && array_key_exists('loggedin', $app['env']['session']->session) && $app['env']['session']->session['loggedin'] && $this->validateSession()){
            return true;
        } else {
            // n.b. the session is cleared by isSessionRecent if invalid

            return $this->loginFromCookie();
        }
    }

    /**
     * Public: Set user
     */
    public function setUser($username)
    {
        $app = App::getInstance();

        try {
            parent::__construct($username);
        } catch (\FelixOnline\Exceptions\ModelNotFoundException $e) {
            User::createUser($username);
            parent::__construct($username);
        }

        $this->setVisits($this->getVisits() + 1);
        $this->setIp($app['env']['REMOTE_ADDR']);
        $this->setTimestamp(time());

        $this->save();
    }

    /************************************
     *
     * SESSIONS
     *
     ************************************/

    /**
     * Create session for user
     */
    public function createSession()
    {
        if(!$this->getUser()) {
            throw new \Exceptions\InternalException('Will not create session where no user is set');
        }

        $this->destroySessions(); // Delete old sessions for user

        $app = App::getInstance();

        $sql = $app['safesql']->query(
            "INSERT INTO `login`
            (
                session_id,
                session_name,
                ip,
                browser,
                user,
                logged_in,
                deleted
            ) VALUES (
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                1,
                0
            )",
            array(
                $app['env']['session']->getId(),
                SESSION_NAME,
                $app['env']['REMOTE_ADDR'],
                $app['env']['HTTP_USER_AGENT'],
                $this->getUser(),
            ));
        $app['db']->query($sql);

        $_SESSION[SESSION_NAME]['uname'] = $this->getUser();
        $_SESSION[SESSION_NAME]['loggedin'] = true;

        $app['env']['session']['uname'] = $this->getUser();
        $app['env']['session']['loggedin'] = true;
    }

    /*
     * Public: Get Session object for active session.
     */
    public function getSession() {
        return $this->session;
    }

    /*
     * Protected: Check if the session is recent (the last visited time is updated
     * on every visit, if this is greater than two hours then we need to log in
     * again, unless the cookie is valid) and valid for this user.
     */
    protected function validateSession()
    {
        $app = App::getInstance();

        $sql = $app['safesql']->query(
            "SELECT
                TIMESTAMPDIFF(SECOND,timestamp,NOW()) AS timediff,
                ip,
                browser
            FROM `login`
            WHERE session_id = '%s'
            AND session_name = '%s'
            AND logged_in = 1
            AND valid = 1
            AND user = '%s'
            AND deleted = 0
            ORDER BY timediff ASC
            LIMIT 1",
            array(
                $app['env']['session']->getId(),
                SESSION_NAME,
                $app['env']['session']->session['uname'],
            ));

        $user = $app['db']->get_row($sql);

        if (
            $user
            && $user->timediff <= SESSION_LENGTH
            && $user->ip == $app['env']['REMOTE_ADDR']
            && $user->browser == $app['env']['HTTP_USER_AGENT']
        ) {
            return true;
        } else {
            $this->resetSession(); // Clear invalid session data
            // N.B. Do not delete cookies here!! If the session is invalid
            // it may have expired, but then we may be able to log in again
            // from the cookie
            return false;
        }
    }

    /*
     * Public: Set aside session so we can reactivate it on the main site
     */
    public function stashSession() {
        $app = App::getInstance();

        $sessionid = $app['env']['session']->getId();

        if($this->resetSession(false)) {
            return $sessionid;
        } else {
            return false;
        }
    }

    /*
     * Public: Reactivate stashed session
     */
    public function restoreSession($existing_id) {
        $app = App::getInstance();

        // Validate our existing ID
        $sql = $app['safesql']->query("SELECT
                                        user,
                                        TIMESTAMPDIFF(SECOND,timestamp,NOW()) AS timediff,
                                        ip,
                                        browser
                                    FROM `login`
                                    WHERE session_id='%s'
                                    AND session_name='%s'
                                    AND valid=1
                                    AND logged_in=0
                                    AND deleted = 0
                                    ORDER BY timediff ASC
                                    LIMIT 1",
                                    array($existing_id, SESSION_NAME));
        $login = $app['db']->get_row($sql);

        if (LOCAL) {
            // Local testing only - force ::1 to IPv4 to avoid a transient bug
            if($login->ip == '127.0.0.1' && $app['env']['REMOTE_ADDR'] == '::1') {
                $ip = '::1';
            } elseif($login->ip == '::1' && $app['env']['REMOTE_ADDR'] == '127.0.0.1') {
                $ip = '127.0.0.1';
            } else {
                $ip = $login->ip;
            }
        } else {
            $ip = $login->ip;
        }

        if(
            $login->timediff <= LOGIN_CHECK_LENGTH
            && $ip == $app['env']['REMOTE_ADDR']
            && $login->browser == $app['env']['HTTP_USER_AGENT']
        ) {
            // Sessions is valid, now reconfigure it for the current session
            $user = $login->user;

            // Clear old ID
            $sql = $app['safesql']->query("DELETE FROM `login`
                                        WHERE session_id='%s'
                                        AND session_name='%s'
                                        AND valid=1
                                        AND logged_in=0
                                        AND deleted=0",
                                        array($existing_id, SESSION_NAME));
            $login = $app['db']->query($sql);

            $this->setUser($user);
            $this->createSession();

            return true;
        } else {
            return array($login->timediff, LOGIN_CHECK_LENGTH, $ip, $app['env']['REMOTE_ADDR'], $login->browser, $app['env']['HTTP_USER_AGENT']);
        }
    }

    public function resetSession($flushdb = true) {
        $app = App::getInstance();

        $sessionid = $app['env']['session']->getId();

        $app['env']['session']->reset();

        // Do we invalidate this session ID?
        if($flushdb) {
            $sql = $app['safesql']->query("UPDATE `login`
                    SET valid = 0,
                    logged_in = 0
                    WHERE user='%s'
                    AND session_id='%s'
                    AND session_name='%s'
                    AND deleted = 0",
                    array($this->getUser(), $sessionid, SESSION_NAME));

            return $app['db']->query($sql);
        } else {
            $sql = $app['safesql']->query("UPDATE `login`
                    SET valid = 1,
                    logged_in = 0
                    WHERE user='%s'
                    AND session_id='%s'
                    AND session_name='%s'
                    AND deleted = 0",
                    array($this->getUser(), $sessionid, SESSION_NAME));

            return $app['db']->query($sql);
        }
    }

    /*
     * Private: Destroy all user sessions
     */
    private function destroySessions() {
        $app = App::getInstance();

        $sql = $app['safesql']->query("UPDATE `login`
                SET valid = 0,
                logged_in = 0
                WHERE user='%s'
                AND session_name='%s'
                AND deleted = 0",
                array($this->getUser(), SESSION_NAME));

        return $app['db']->query($sql);
    }

    /*
     * Private: Destroy old sessions
     */
    private function destroyOldSessions() {
        $sql = "DELETE FROM cookies
                WHERE UNIX_TIMESTAMP() > UNIX_TIMESTAMP(expires)
        ";
        $this->db->query($sql);
        $sql = $this->safesql->query("UPDATE `login`
                SET valid = 0
                WHERE user='%s'
                AND deleted = 0
                AND session_name='%s'
                AND logged_in=0
                OR TIMESTAMPDIFF(SECOND,timestamp,NOW()) > %i",
                array($this->getUser(),
                        SESSION_NAME,
                        SESSION_LENGTH));

        return $this->db->query($sql);
    }

    /************************************
     *
     * COOKIES
     *
     ************************************/

    /**
     * Public: Removes the permanent cookie, and removes associated database entries
     */
    public function removeCookie()
    {
        $app = App::getInstance();

        $sql = $app['safesql']->query(
            "UPDATE cookies
            SET deleted = 1
            WHERE hash = '%s'",
            array(
                $app['env']['cookies'][COOKIE_NAME]
            ));

        $app['db']->query($sql);

        // also remove any expired cookies for anyone
        // TODO move to cron
        $sql = $app['safesql']->query(
            "UPDATE cookies
            SET deleted = 1
            WHERE expires < NOW()", array());

        $app['db']->query($sql);

        $app['env']['cookies']->delete(COOKIE_NAME);
    }

    /*
     * Public: Create cookie
     */
    public function setCookie()
    {
        $app = App::getInstance();

        $hash = hash('sha256', mt_rand());

        $expiry_time = time() + COOKIE_LENGTH;

        $app['env']['cookies']->set(COOKIE_NAME, $hash, $expiry_time, '/');
        $sql = $app['safesql']->query("INSERT INTO `cookies`
                                    (
                                        hash,
                                        user,
                                        expires,
                                        deleted
                                    ) VALUES (
                                        '%s',
                                        '%s',
                                        FROM_UNIXTIME(%i)
                                        0
                                    )",
                                    array($hash, $this->getUser(), $expiry_time));

        return $app['db']->query($sql);
    }

    /**
     * Protected: Check if there is a valid permanent cookie, if so log in with it
     *
     * Returns false if failed, username otherwise
     */
    protected function loginFromCookie()
    {
        $app = App::getInstance();

        // is there a cookie?
        if (!isset($app['env']['cookies'][COOKIE_NAME])) {
            return false;
        }

        $sql = $app['safesql']->query(
            "SELECT user
            FROM `cookies`
            WHERE hash='%s'
            AND UNIX_TIMESTAMP(expires) > UNIX_TIMESTAMP()
            AND deleted = 0
            ORDER BY expires ASC
            LIMIT 1",
            array(
                $app['env']['cookies'][COOKIE_NAME]
            ));

        $cookie = $app['db']->get_row($sql);
        if (!$cookie) {
            $this->removeCookie();
            return false;
        }

        $username = $cookie->user;

        // Reset session ID
        $this->resetSession();

        // Populate user class
        $this->setUser($username);

        // Create session
        $this->createSession();

        return true;
    }
}
