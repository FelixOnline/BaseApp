<?php
namespace FelixOnline\Core;

use FelixOnline\Exceptions\InternalException;

class HttpEnvironment implements EnvironmentInterface, \ArrayAccess {
    protected $properties;
    private $request;
    private $response;
    private $emitter;
    private $glue;

    public function __construct() {
        $env = array();

        if(php_sapi_name() == "cli") {
            // Remote params
            $env['Method'] = 'CLI';
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

        $this->request = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );
        $this->response = new \Zend\Diactoros\Response\HtmlResponse('');
        // Override response if not html

        $this->emitter = new \Zend\Diactoros\Response\SapiEmitter();

        $this->glue = new HttpGlue();
        $this->glue->addMiddleware(new \Psr7Middlewares\Middleware\PhpSession(SESSION_NAME));
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
        if(!($response instanceof \Psr\Http\Message\ResponseInterface)) {
            throw new InternalException('Not a PSR-7 Response');
        }

        $this->response = $response;
    }

    public function getGlue() {
        return $this->glue;
    }

    public function dispatch() {
        if(!($this->response instanceof \Psr\Http\Message\ResponseInterface)) {
            throw new InternalException('Set a PSR-7 Response');
        }
        $this->response = $this->getGlue()->dispatch($this->request, $this->response);
    }

    public function dispatchAndEmit() {
        $this->dispatch();
        $this->emit();
    }

    public function emit() {
        $this->emitter->emit($this->response);
    }

    public function terminate() {
        exit();
    }
}
