<?php
namespace FelixOnline\Core;

class HttpEnvironment implements EnvironmentInterface, \ArrayAccess {
    protected $properties;

    public function __construct() {
        $env = array();

        if(php_sapi_name() == "cli") {
            // Remote params
            $env['Method'] = 'GET';
            $env['RemoteIP'] = '127.0.0.1';
            $env['RemoteUA'] = 'Cli/1.0';

            // Request
            $env['ScriptName'] = 'cli';
            $env['RequestUri'] = 'cli';
            $env['QueryString'] = '';
        } else {
            // Remote params
            $env['Method'] = $_SERVER['REQUEST_METHOD'];
            $env['RemoteIP'] = $_SERVER['REMOTE_ADDR'];
            $env['RemoteUA'] = $_SERVER['HTTP_USER_AGENT'];

            // Request
            $env['ScriptName'] = $_SERVER['SCRIPT_NAME'];
            $env['RequestUri'] = $_SERVER['REQUEST_URI'];
            $env['QueryString'] = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        }

        $this->properties = $env;
    }

    public function offsetSet($offset, $value) {
        if(is_null($offset)) {
            $this->properties[] = $value;
        } else {
            $this->properties[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->properties[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->properties[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->properties[$offset]) ? $this->properties[$offset] : null;
    }
}
