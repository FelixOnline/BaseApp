<?php

require_once __DIR__ . '/DatabaseTestCase.php';
require_once __DIR__ . '/../lib/SafeSQL.php';

/**
 * App test case - extend from this for anything requiring the App class.
 */
class AppTestCase extends DatabaseTestCase {
    use \Xpmock\TestCaseTrait;

    protected $appConfig = array();
    protected $app;
    protected $setCurrentUser = true; // whether to initialise a current user or not

    /*
     * Set up an instance of App, and connect to the database.
     */
    public function setUp() {
        parent::setUp();

        // create app
        $config = $this->appConfig + array(
            'base_url' => 'http://localhost/'
        );

        $app = new \FelixOnline\Core\App($config);

        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $db = new \ezSQL_mysqli();
        $db->quick_connect(
            $dbuser,
            $dbpass,
            'test_media_felix',
            'localhost',
            3306,
            'utf8'
        );
        $app['db'] = $db;

        $app['safesql'] = new \SafeSQL_MySQLi($db->dbh);

        $app['env'] = \FelixOnline\Core\Environment::mock();

        // Initialize Akismet
        $connector = new \Riv\Service\Akismet\Connector\Test();
        $app['akismet'] = new \Riv\Service\Akismet\Akismet($connector);

        // Initialize email
        $transport = \Swift_NullTransport::newInstance();
        $app['email'] = \Swift_Mailer::newInstance($transport);

        $session = $this->mock('FelixOnline\\Core\\Session')
            ->getId(1)
            ->start(1)
            ->reset()
            ->new();

        $this->reflect($session)
            ->__set('session', array());

        $app['env']['session'] = $session;

        $cookies = $this->mock('FelixOnline\\Core\\Cookies')
            ->set(true)
            ->delete(true)
            ->new();

        $this->reflect($cookies)
            ->__set('cookies', array());

        $app['env']['cookies'] = $cookies;

        if ($this->setCurrentUser) {
            $app['currentuser'] = new \FelixOnline\Core\CurrentUser();
        }

        // Set empty cache so data isn't cached in tests
        $app['cache'] = new \Stash\Pool();

        $app->run();

        $this->app = $app;
    }

    /*
     * Close down App.
     */
    public function tearDown() {
        parent::tearDown();

        $app = \FelixOnline\Core\App::getInstance();

        $app['db']->dbh->close();

        \FelixOnline\Core\App::setInstance(null);
    }
}
