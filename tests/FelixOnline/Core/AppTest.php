<?php
require_once __DIR__ . '/../../../lib/SafeSQL.php';
require_once __DIR__ . '/../../DatabaseTestCase.php';

/*
 * As this is a test of the App class, we cannot rely on its operation, and
 * therefore we don't extend AppTestCase.
 */
class AppTest extends DatabaseTestCase {
    use \Xpmock\TestCaseTrait;

    public function createApp($config) {
        $app = new \FelixOnline\Core\App($config);

        $app['env'] = new \FelixOnline\Core\HttpEnvironment();

        $session = $this->mock('FelixOnline\\Core\\Session')
            ->getId(1)
            ->start(1)
            ->reset()
            ->new();

        $this->reflect($session)
            ->__set('session', array());

        $app['env']['session'] = $session;

        $app->run();

        return $app;
    }

    public function testApp() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));
        $this->assertInstanceOf('FelixOnline\Core\App', $app);
    }

    public function testAppWithCacheFolder() {

        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false,
            'stash_cache_folder' => '/tmp'
        ));
        $this->assertInstanceOf('FelixOnline\Core\App', $app);
    }

    public function testSingleton() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));

        $this->assertEquals($app, \FelixOnline\Core\App::getInstance());
    }

    public function testAccessBeforeInit() {
        \FelixOnline\Core\App::setInstance(null);

        $this->setExpectedException(
            'Exception',
            'App has not been initialised yet'
        );

        $app = \FelixOnline\Core\App::getInstance();
    }

    public function testRequiredOptions() {
        $this->setExpectedException(
            'Exception',
            '"db_name" option has not been defined'
        );

        $app = $this->createApp(array());
    }

    public function testGetOption() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));

        $this->assertEquals($app->getOption('base_url'), 'http://localhost/');
    }

    public function testGetOptionDefault() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));

        $this->assertEquals($app->getOption('foo', 'bar'), 'bar');
    }

    public function testGetOptionException() {
        $this->setExpectedException(
            'FelixOnline\Exceptions\InternalException',
            'Option "bar" has not been set'
        );

        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));

        $app->getOption('bar');
    }

    public function testRunInvalidDBDetailsException() {
        \FelixOnline\Core\App::setInstance(null);

        $this->setExpectedException(
            'Exception',
            'Could not connect to database'
        );

        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => 'INVALID',
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));

        $app->run();
    }

    public function testQuery() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));


        $this->assertEquals(
            "SELECT id FROM foo",
            $app['safesql']->query("SELECT id FROM %s", array("foo"))
        );
    }

    public function testNotSetException() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));


        $this->setExpectedException(
            'FelixOnline\Exceptions\InternalException',
            'Key "foo" is not set'
        );

        // Try and access key that doesn't exist
        $app['foo'];
    }

    public function testUnset() {
        $dbuser = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $dbpass = getenv('DB_PASS') ? getenv('DB_PASS') : '';

        $app = $this->createApp(array(
            'base_url' => 'http://localhost/',
            'db_user' => $dbuser,
            'db_pass' => $dbpass,
            'db_name' => 'test_media_felix',
            'db_host' => 'localhost',
            'unit_tests' => true,
            'production' => false
        ));

        $app['foo'] = 'bar';

        $this->assertTrue(isset($app['foo']));

        unset($app['foo']);

        $this->assertFalse(isset($app['foo']));
    }
}
