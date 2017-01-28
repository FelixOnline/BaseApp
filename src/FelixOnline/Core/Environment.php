<?php
namespace FelixOnline\Core;
/**
 * Environment
 */
class Environment implements \ArrayAccess
{
	protected $properties;
	protected static $environment;

	/**
     * Get environment instance (singleton)
     *
     * This creates and/or returns an environment instance (singleton)
     * derived from $_SERVER variables. You may override the global server
     * variables by using `\Slim\Environment::mock()` instead.
     *
     * @param  bool             $refresh Refresh properties using global server variables?
     * @return Environment
     */
    public static function getInstance($refresh = false)
    {
        if (is_null(self::$environment) || $refresh) {
            self::$environment = new self();
        }

        return self::$environment;
    }

	/**
     * Get mock environment instance
     *
     * @param  array       $userSettings
     * @return Environment
     */
    public static function mock($userSettings = array())
    {
        $defaults = array(
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '',
            'QUERY_STRING' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'USER_AGENT' => 'Slim Framework',
            'REMOTE_ADDR' => '0.0.0.0',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1764.0 Safari/537.36'
        );
        self::$environment = new self(array_merge($defaults, $userSettings));

        return self::$environment;
    }

	/**
     * Constructor (private access)
     *
     * @param  array|null $settings If present, these are used instead of global server variables
	 *
	 * @codeCoverageIgnore
     */
    private function __construct($settings = null)
    {
		if ($settings) {
            $this->properties = $settings;
        } else {
            $env = array();

            // The HTTP request method
            $env['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

            // The IP
            $env['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];

			// User agent
            $env['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

            // Server params
            $scriptName = $_SERVER['SCRIPT_NAME']; // <-- "/foo/index.php"
            $requestUri = $_SERVER['REQUEST_URI']; // <-- "/foo/bar?test=abc" or "/foo/index.php/bar?test=abc"
            $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''; // <-- "test=abc" or ""

            // Physical path
            if (strpos($requestUri, $scriptName) !== false) {
                $physicalPath = $scriptName; // <-- Without rewriting
            } else {
                $physicalPath = str_replace('\\', '', dirname($scriptName)); // <-- With rewriting
            }
            $env['SCRIPT_NAME'] = rtrim($physicalPath, '/'); // <-- Remove trailing slashes

            // Virtual path
            $env['PATH_INFO'] = substr_replace($requestUri, '', 0, strlen($physicalPath)); // <-- Remove physical path
            $env['PATH_INFO'] = str_replace('?' . $queryString, '', $env['PATH_INFO']); // <-- Remove query string
            $env['PATH_INFO'] = '/' . ltrim($env['PATH_INFO'], '/'); // <-- Ensure leading slash

            // Query string (without leading "?")
            $env['QUERY_STRING'] = $queryString;

            // Name of server host that is running the script
            $env['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

            // Number of server port that is running the script
            $env['SERVER_PORT'] = $_SERVER['SERVER_PORT'];

            $this->properties = $env;
        }
    }


	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
            $this->properties[] = $value;
        } else {
            $this->properties[$offset] = $value;
        }
    }

	public function offsetExists($offset)
	{
		return isset($this->properties[$offset]);
	}

	public function offsetUnset($offset)
	{
        unset($this->properties[$offset]);
    }

	public function offsetGet($offset)
	{
        return isset($this->properties[$offset]) ? $this->properties[$offset] : null;
    }
}
