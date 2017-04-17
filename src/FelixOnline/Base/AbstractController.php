<?php
namespace FelixOnline\Base;

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
    }
}
