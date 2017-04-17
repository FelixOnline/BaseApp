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

        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        // create app
        $config = $this->appConfig + array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        );

        $app = new \FelixOnline\Base\App($config);

        $app['env'] = new \FelixOnline\Base\HttpEnvironment();

        // Initialize Akismet
        $connector = new \Riv\Service\Akismet\Connector\Test();
        $app['akismet'] = new \Riv\Service\Akismet\Akismet($connector);

        // Initialize email
        $transport = \Swift_NullTransport::newInstance();
        $app['email'] = \Swift_Mailer::newInstance($transport);

        $session = $this->mock('FelixOnline\\Base\\Session')
            ->getId(1)
            ->start(1)
            ->reset()
            ->new();

        $this->reflect($session)
            ->__set('session', array());

        $app['env']['session'] = $session;

        if ($this->setCurrentUser) {
            $app['currentuser'] = new \FelixOnline\Base\StubCurrentUser();
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

        $app = \FelixOnline\Base\App::getInstance();

        $app['db']->dbh->close();

        \FelixOnline\Base\App::setInstance(null);
    }
}
