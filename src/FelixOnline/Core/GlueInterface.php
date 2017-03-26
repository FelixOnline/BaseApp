<?php
namespace FelixOnline\Core;

interface GlueInterface {
    public function __construct();

    public function addMiddleware($middleware);

    public function mapRoute($method, $path, $class, $classMethod, $middleware = false);

    public function mapRoutes(array $routes);

    public function dispatch($request, $response);
}
