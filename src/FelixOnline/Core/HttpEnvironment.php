<?php
namespace FelixOnline\Core;

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

        $this->request = \Zend\Diactoros\Response::class;
        $this->response = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );
        $this->emitter = \Zend\Diactoros\Response\SapiEmitter::class;

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
        if(!($response instanceof \Psr\Http\Message\ServerRequestInterface)) {
            throw new Exceptions\InternalException('Not a PSR-7 ServerRequest');
        }

        $this->response = $response;
    }

    public function getGlue() {
        return $this->glue;
    }

    public function dispatch() {
        $this->response = $this->getGlue()->dispatch($this->request, $this->response);
    }

    public function dispatchAndEmit() {
        $this->dispatch();
        $this->emit();
    }

    public function emit() {
        $this->emitter->emit($this->response);
    }

    public function exit() {
        exit;
    }

    /* Cookies */
    public function getCookie($cookie) {
        return \Dflydev\FigCookies\FigRequestCookies::get($this->request, $cookie);
    }

    public function setCookie(\DFlydev\FigCookies\SetCookie $cookie) {
        $this->response = \Dflydev\FigCookies\FigResponseCookies::set(
            $this->response,
            $cookie
        );
    }

    public function removeCookie($cookie) {
        $this->response = \Dflydev\FigCookies\FigResponseCookies::remove(
            $this->response,
            $cookie
        );
    }

    public function expireCookie($cookie) {
        $this->response = \Dflydev\FigCookies\FigResponseCookies::expire(
            $this->response,
            $cookie
        );
    }

    public function modifyCookie(\DFlydev\FigCookies\SetCookie $cookie) {
        $this->response = \Dflydev\FigCookies\FigResponseCookies::modify(
            $this->response,
            $cookie->getName(),
            function($cookie) { return $cookie; }
        );
    }
}
