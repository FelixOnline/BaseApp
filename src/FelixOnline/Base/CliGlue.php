<?php
namespace FelixOnline\Base;

use FelixOnline\Exceptions\GlueInternalException;
use FelixOnline\Exceptions\GlueMethodException;
use FelixOnline\Exceptions\InternalException;

class CliGlue implements GlueInterface {
    private $climate;
    private $routes = array();

    public function __construct() {
        $this->climate = new \League\CLImate\CLImate;
    }

    public function addMiddleware($middleware) {
        throw new InternalException(
            'Middleware is not supported for CLI'
        );
    }

    // method is the command name (e.g. useradd)
    // path is the command information (e.g. Add a user)
    public function mapRoute($method, $path, $class, $classMethod, $middleware = false) {
        if($method == 'help') {
            throw new GlueInternalException(
                'Do not map "help" as this is internally reserved.',
                $method,
                $class,
                $classMethod
            );
        }

        if($middleware) {
            throw new InternalException(
                'Middleware is not supported for CLI'
            );
        }

        // Check that handler exists
        if(!class_exists($class)) { // FIXME: implements
            throw new GlueInternalException(
                'Class does not exist.',
                $method,
                $class,
                $classMethod
            );
        }

        $obj = new $class;
        if(!method_exists($obj, $classMethod)) {
            throw new GlueMethodException(
                'Method does not exist.',
                $method,
                $class,
                $classMethod
            );
        }

        $this->routes[$method] = array(
            'Command' => $method, // Defined here too for help
            'Description' => $path,
            'Class' => $class,
            'Method' => $classMethod
        );
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

    public function dispatch($request, $response) {
        $runHelp = false;

        if($request['CountArguments'] == 0) {
            $this->climate->error('Please specify a command. (try running help.)');
            return 65; // EX_DATAERR per BSD sysexits.h
        }

        $method = $request['Arguments'][0];

        if(count($this->routes) == 0) {
            // no routes msg
            $this->climate->error('No cli commands defined.');
            return 70; // EX_SOFTWARE per BSD sysexits.h
        }

        if(strtolower($method) == "help") {
            $runHelp = true;
        } else {
            if(!array_key_exists($method, $this->routes)) {
                // no routes msg
                $this->climate->error('This command does not exist. (Try running help.)');
                return 65; // EX_DATAERR per BSD sysexits.h
            }
        }

        if($runHelp) {
            $app = App::getInstance();
            $appName = $app->getOption('app_name');
            $appVer = $app->getOption('app_version');

            if($request['CountArguments'] == 1) {
                // general help
                $climate = $this->climate;
                $climate->bold('CLI Command Reference - '.$appName.' '.$appVer);
                $climate->out('The following commands have been defined.');
                $climate->nl();

                if($app->getOption('production')) {
                    $routes = array();
                    foreach($this->routes as $route) {
                        $routes[] = array(
                            'Command' => $route['Command'],
                            'Description' => $route['Description']
                        );
                    }
                    $climate->table($routes);
                } else {
                    $climate->table($this->routes);
                }

                $climate->nl();
                $climate->out('For usage information for a command, run <bold>help <command></bold>.');
            } else {
                $helpMethod = $request['Arguments'][1];

                if($helpMethod == 'help') {
                    $this->climate->description('Displays details on installed commands and their usage.');

                    $climate = $this->climate;
                } else {
                    if(!array_key_exists($helpMethod, $this->routes)) {
                        // no routes msg
                        $this->climate->error('This command does not exist. (Try running help.)');
                        return 65; // EX_DATAERR per BSD sysexits.h
                    }

                    $this->climate->description($this->routes[$helpMethod]['Description']);

                    $class = new $this->routes[$helpMethod]['Class']($this->climate);
                    $classMethod = $this->routes[$helpMethod]['Method'];

                    // Special case of array(), true just sets up climate with parameters and returns
                    $climate = $class->$classMethod(array(), true);
                }

                $climate->usage();
            }

            return 64; // EX_USAGE per BSD sysexits.h
        }

        try {
            $class = new $this->routes[$method]['Class']($this->climate);
            $classMethod = $this->routes[$method]['Method'];
            $response = $class->$classMethod($request['Arguments']);
        } catch(\Exception $e) {
            // last resort
            $this->climate->error($e->getMessage().' (Try <bold>help '.$method.'</bold> for usage.)');
            $response = 70; // EX_SOFTWARE per BSD sysexits.h
        }

        return $response; // Set error status, should be 0 if success
    }
}
