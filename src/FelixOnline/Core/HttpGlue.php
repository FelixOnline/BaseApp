<?php
namespace FelixOnline\Core;

/**
 * HttpGlue
 *
 * A URL mapper wrapper on top of League\Route
 */

use FelixOnline\Exceptions\GlueInternalException;
use FelixOnline\Exceptions\GlueMethodException;
use FelixOnline\Exceptions\GlueRouteException;

class HttpGlue {
    private $router;

    public function __construct() {
        $this->router = new \League\Route\RouteCollection();
    }

    public function addMiddleware($middleware) {
        $this->router->middleware($middleware);
    }

    public function mapRoute($method, $path, $class, $classMethod, $middleware = false) {
        // Check that handler exists
        if(!class_exists($class)) {
            throw new GlueInternalException(
                'Class does not exist.',
                $path,
                $class,
                $classMethod
            );
        }

        $obj = new $class;
        if(!method_exists($obj, $classMethod)) {
            throw new GlueMethodException(
                'Method does not exist in.',
                $path,
                $class,
                $classMethod
            );
        }

        // Tests pass, now add to router
        if($middleware) {
            // FIXME: only one middleware per route
            $this->router->map($method, $path, [new $class, $classMethod])
                         ->middleware($middleware);
        } else {
            $this->router->map($method, $path, [new $class, $classMethod]);
        }
    }

    public function mapRoutes(array $routes) {
        /*
         * 0: method
         * 1: path
         * 2: class
         * 3: classMethod
         * 4: middleware
         */

        foreach($routes as $route) {
            $this->mapRoute($route[0], $route[1], $route[2], $route[3], $route[4]);
        }
    }

    public function dispatch(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        return $this->router->dispatch($request, $response);

        if(!$found) {
            throw new GlueURLException("The URL accessed does not match any URL in the glue", $path);
        }
    }
}