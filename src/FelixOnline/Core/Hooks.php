<?php
namespace FelixOnline\Core;
/*
 * Action hooks
 *
 * Usage:
 *      $hooks->addAction('UNIQUE_IDENTIFIER', 'FUNCTION_NAME');
 *      $funcname = $hooks->getAction('UNIQUE_IDENTIFIER');
 *      call_user_func($funcname);
 */
class Hooks {
    private $actions = array(); // stores actions
    private $protected = array();

    /*
     * Public: Add action
     */
    public function addAction($action, $function, $protect = true) {
        $this->actions[$action] = $function;
        $this->protected[$action] = $protect;

        return $this->actions;
    }

    public function getAction($action) {
        if(array_key_exists($action, $this->actions)) {
            return $this->actions[$action];
        } else {
            return false;
        }
    }

    public function isProtected($action) {
        if(array_key_exists($action, $this->protected)) {
            return $this->protected[$action];
        } else {
            return true;
        }
    }
}
