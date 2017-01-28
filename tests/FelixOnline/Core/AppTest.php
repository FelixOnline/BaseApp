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
		$app = $this->createApp(array(
			'base_url' => 'foo'
		));
		$this->assertInstanceOf('FelixOnline\Core\App', $app);
	}

	public function testAppWithCacheFolder() {
		define('CACHE_FOLDER', '.');

		$app = $this->createApp(array(
			'base_url' => 'foo'
		));
		$this->assertInstanceOf('FelixOnline\Core\App', $app);
	}

	public function testSingleton()	{
		$app = $this->createApp(array(
			'base_url' => 'foo'
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
			'"base_url" option has not been defined'
		);

		$app = $this->createApp(array());
	}

	public function testGetOption()	{
		$app = $this->createApp(array(
			'base_url' => 'foo'
		));

		$this->assertEquals($app->getOption('base_url'), 'foo');
	}

	public function testGetOptionDefault() {
		$app = $this->createApp(array(
			'base_url' => 'foo'
		));
		$this->assertEquals($app->getOption('foo', 'bar'), 'bar');
	}

	public function testGetOptionException() {
		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'Option "bar" has not been set'
		);

		$app = $this->createApp(array(
			'base_url' => 'foo',
		));

		$app->getOption('bar');
	}

	public function testRunNoDbException() {
		\FelixOnline\Core\App::setInstance(null);

		$this->setExpectedException(
			'Exception',
			'No db setup'
		);

		$app = new \FelixOnline\Core\App(array(
			'base_url' => 'foo'
		));
		$app->run();
	}

	public function testRunWrongDbTypeException() {
		\FelixOnline\Core\App::setInstance(null);

		$this->setExpectedException(
			'Exception',
			'No db setup'
		);

		$app = new \FelixOnline\Core\App(array(
			'base_url' => 'foo'
		));
		$app['db'] = 'foo';
		$app->run();
	}

	public function testRunNoSafesqlException()	{
		\FelixOnline\Core\App::setInstance(null);

		$this->setExpectedException(
			'Exception',
			'No safesql setup'
		);

		$app = new \FelixOnline\Core\App(array(
			'base_url' => 'foo'
		));

		$db = new \ezSQL_mysqli();
		$app['db'] = $db;
		$app->run();
	}

	public function testRunNoWrongSafesqlException() {
		\FelixOnline\Core\App::setInstance(null);

		$this->setExpectedException(
			'Exception',
			'No safesql setup'
		);

		$app = new \FelixOnline\Core\App(array(
			'base_url' => 'foo'
		));

		$db = new \ezSQL_mysqli();
		$app['db'] = $db;
		$app['safesql'] = 'foo';
		$app->run();
	}

	public function testQuery()	{
		$app = $this->createApp(array(
			'base_url' => 'foo',
		));

		$this->assertEquals(
			"SELECT id FROM foo",
			$app['safesql']->query("SELECT id FROM %s", array("foo"))
		);
	}

	public function testNotSetException() {
		$app = $this->createApp(array(
			'base_url' => 'foo',
		));

		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'Key "foo" is not set'
		);

		// Try and access key that doesn't exist
		$app['foo'];
	}

	public function testUnset() {
		$app = $this->createApp(array(
			'base_url' => 'foo',
		));

		$app['foo'] = 'bar';

		$this->assertTrue(isset($app['foo']));

		unset($app['foo']);

		$this->assertFalse(isset($app['foo']));
	}
}
