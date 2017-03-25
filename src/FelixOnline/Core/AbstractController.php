<?php
namespace FelixOnline\Core;

/*
 * Abstract Controller
 */

abstract class AbstractController {
    protected $theme; // placeholder for theme class

    function __construct($currTheme) {
        /*
         * Set theme here so that it can be overridden by a controller if necessary
         */
        $theme = new Theme($currTheme);
        $this->theme = $theme->getClass(); // used so that theme can specify a theme class if necessary
        $this->theme->setSite('main');

        $app = App::getInstance();

        if($app->getMode() == App::MODE_HTTP) {
            $app['env']->startBuffer();
            $app['env']['session']->start();
        }
    }

    function HEAD($matches) {
        // Used by updowntester
        $app = App::getInstance();

        if($app->getMode() == App::MODE_CLI) {
            throw new Exceptions\InternalException('Should not run HEAD on Controller if CLI');
        }

        $app['env']->exit();

    }

    abstract public function CLI($matches);
    abstract public function GET($matches);
    abstract public function POST($matches);
}
