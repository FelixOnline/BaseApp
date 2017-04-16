<?php
namespace FelixOnline\Core;

use FelixOnline\Exceptions\InternalException;

class CliEnvironment implements EnvironmentInterface, \ArrayAccess {
    protected $properties;
    private $glue;

    public function __construct() {
        global $argv;
        global $argc;

        array_shift($argv);
        $argc--;

        $env['Arguments'] = $argv;
        $env['CountArguments'] = $argc;

        $this->properties = $env;

        $this->response = "";

        $this->glue = new CliGlue();
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

    public function getResponse() {
        return $this->response;
    }

    public function setResponse($response) {
        $this->response = $response;
    }

    public function getGlue() {
        return $this->glue;
    }

    public function dispatch() {
        $this->response = $this->getGlue()->dispatch(
            $this->properties,
            $this->response
        );
    }

    public function dispatchAndEmit() {
        $this->dispatch();
        $this->emit();
    }

    // The cli function will print its own stuff but respond with an exit status
    public function emit() {
        $this->terminate($this->response);
    }

    public function terminate($status = 0) {
        exit($status);
    }
}
