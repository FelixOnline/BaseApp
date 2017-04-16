<?php
namespace FelixOnline\Core;

use FelixOnline\Exceptions\InternalException;
use FelixOnline\Exceptions\DBConnectionException;

/**
 * App class
 */
class App implements \ArrayAccess {
    protected static $instance = null;
    protected static $options = array();
    protected $container;

    const MODE_HTTP = 0;
    const MODE_CLI = 1;

    /**
     * Required options
     */
    protected $required = array(
        'db_name',
        'db_host',
        'db_user',
        'db_pass',
        'production'
    );

    /**
     * Constructor
     *
     * @param array $options - options array
     */
    public function __construct(
        $options = array()
    ) {
        $this->checkOptions($options);
        self::$options = $options;

        unset($this->container);

        self::$instance = $this;
    }

    /**
     * Initialize app
     */
    public function run() {
        $runner = new \League\BooBoo\Runner();

        if($this->getMode() == self::MODE_HTTP) {
            $aFtr = new \League\BooBoo\Formatter\HtmlTableFormatter;
        } else {
            $aFtr = new \League\BooBoo\Formatter\CommandLineFormatter;
        }

        $null = new \League\BooBoo\Formatter\NullFormatter;

        if($this->getOption('production')) {
            $aFtr->setErrorLimit(E_ERROR | E_USER_ERROR);
        } else {
            $aFtr->setErrorLimit(E_ERROR | E_WARNING | E_USER_ERROR | E_USER_WARNING);
        }
        $null->setErrorLimit(E_ALL);

        $runner->pushFormatter($null);
        $runner->pushFormatter($aFtr);

        if(
            !isset($this->container['sentry']) ||
            !($this->container['sentry'] instanceof \Raven_Client)
        ) {
            if(!isset(self::$options['sentry_dsn'])) {
                $dsn = '';
            } else {
                $dsn = self::$options['sentry_dsn'];
            }

            $app['sentry'] = new \Raven_Client($dsn);
        }

        $raven = new \League\BooBoo\Handler\RavenHandler($app['sentry']);
        $runner->pushHandler($raven);

        $runner->register();

        $this->container['booboo'] = $runner;

        if(
            !isset($this->container['env'])
            || is_null($this->container['env'])
        ) {
            if(
                $this->getMode() == self::MODE_HTTP ||
                $this->isRunningUnitTests()
            ) {
                try {
                    $this->getOption('base_url');
                } catch(\Exception $e) {
                    throw new InternalException('base_url must be set when running in a HTTP or Unit Test environment');
                }

                $this->container['env'] = new HttpEnvironment();
            } else {
                $this->container['env'] = new CliEnvironment();
            }
        }

        if(
            !isset($this->container['akismet']) ||
            is_null($this->container['akismet'])
        ) {
            // Initialize Akismet
            if(self::$options['production']) {
                $connector = new \Riv\Service\Akismet\Connector\Curl();
            } else {
                $connector = new \Riv\Service\Akismet\Connector\Test();
            }

            $this->container['akismet'] = new \Riv\Service\Akismet\Akismet($connector);
        }

        if(
            !isset($this->container['email']) ||
            is_null($this->container['email'])
        ) {
            // Initialize email
            $transport = \Swift_MailTransport::newInstance();
            $this->container['email'] = \Swift_Mailer::newInstance($transport);
        }

        if(
            !isset($this->container['cache']) ||
            is_null($this->container['cache'])
        ) {
            if(!(self::$options['production'])) {
                $driver = new \Stash\Driver\BlackHole();
            } else {
                if(!isset(self::$options['stash_cache_folder'])) {
                    $driver = new \Stash\Driver\FileSystem(array('path' => self::$options['stash_cache_folder']));
                } else {
                    $driver = new \Stash\Driver\FileSystem();
                }
            }

            $this->container['cache'] = new \Stash\Pool($driver);
        }

        if(
            !isset($this->container['currentuser'])
            || is_null($this->container['currentuser'])
            || !($this->container['currentuser'] instanceof AbstractCurrentUser)
        ) {
            $this->container['currentuser'] = new StubCurrentUser();
        }

        if(
            !isset($this->container['db']) ||
            !($this->container['db'] instanceof \ezSQL_mysqli)
        ) {
            $db = new \ezSQL_mysqli();
            $status = $db->quick_connect(
                self::$options['db_user'],
                self::$options['db_pass'],
                self::$options['db_name'],
                self::$options['db_host'],
                self::$options['db_port'],
                'utf8'
            );

            if(!$status) {
                throw new DBConnectionException('Could not connect to database');
            }

            $db->show_errors();

            $this->container['db'] = $db;

            $this->container['db_log'] = array();
        }

        if(
            !isset($this->container['safesql']) ||
            !($this->container['safesql'] instanceof \SafeSQL_MySQLi)
        ) {
            $this->container['safesql'] = new \SafeSQL_MySQLi($this->container['db']->dbh);
        }
    }

    public function getMode() {
        if(php_sapi_name() == "cli") {
            return self::MODE_CLI;
        } else {
            return self::MODE_HTTP;
        }
    }

    public function isRunningUnitTests() {
        try {
            return $this->getOption('unit_tests');
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * Check that all required options are defined
     *
     * Throws Exception if option is not defined
     */
    private function checkOptions($options) {
        foreach($this->required as $req) {
            if(!array_key_exists($req, $options)) {
                throw new \Exception('"' . $req . '" option has not been defined');
            }
        }
    }

    /**
     * Get instance
     *
     * @return instance
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            throw new \Exception('App has not been initialised yet');
        }
        return self::$instance;
    }

    /**
     * Set instance
     *
     * @param object $instance - instance object to set
     *
     * @return void
     */
    public static function setInstance($instance) {
        self::$instance = $instance;
    }

    /**
     * Get option
     *
     * @param string $key - option key
     *
     * @return mixed option
     */
    public function getOption($key) {
        if (!array_key_exists($key, self::$options)) {
            // if a default has been defined
            if (func_num_args() > 1) {
                return func_get_arg(1);
            } else {
                throw new InternalException('Option "'.$key.'" has not been set');
            }
        }
        return self::$options[$key];
    }

    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        if(!isset($this->container[$offset])) {
            throw new InternalException('Key "' . $offset . '" is not set');
        }
        return $this->container[$offset];
    }
}
