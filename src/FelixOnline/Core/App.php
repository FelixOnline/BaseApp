<?php
namespace FelixOnline\Core;

use FelixOnline\Exceptions\InternalException;

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
        'base_url',
        'db_name',
        'db_host',
        'db_user',
        'db_pass'
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
        if(
            !isset($this->container['env'])
            || is_null($this->container['env'])
        ) {
            $this->container['env'] = new HttpEnvironment();
        }

        if(
            !isset($this->container['akismet']) ||
            is_null($this->container['akismet'])
        ) {
            // Initialize Akismet
            if(LOCAL) {
                $connector = new \Riv\Service\Akismet\Connector\Test();
            } else {
                $connector = new \Riv\Service\Akismet\Connector\Curl();
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
            if(LOCAL) {
                $driver = new \Stash\Driver\BlackHole();
            } else {
                if(defined('CACHE_FOLDER')) {
                    $driver = new \Stash\Driver\FileSystem(array('path' => CACHE_FOLDER));
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
                throw new InternalException('Could not connect to database');
            }

            $db->show_errors();

            $this->container['db'] = $db;
        }

        if(
            !isset($this->container['safesql']) ||
            !($this->container['safesql'] instanceof \SafeSQL_MySQLi)
        ) {
            $safesql = new \SafeSQL_MySQLi($this->container['db']->dbh);
        }

        if(
            !isset($this->container['sentry']) ||
            !($this->container['sentry'] instanceof \Raven_Client)
        ) {
            $app['sentry'] = new \Raven_Client(self::$options['sentry_dsn']);
        }
    }

    public function getMode() {
        if(php_sapi_name() == "cli") {
            return self::MODE_CLI;
        } else {
            return self::MODE_HTTP;
        }
    }

    public function isUnderTest() {
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
     * @param mixed $default - value to return if not defined [optional]
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
