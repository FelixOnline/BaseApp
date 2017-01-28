<?php
namespace FelixOnline\Core;
/**
 * Session class
 */
class Session implements \ArrayAccess
{
    public $session = array();
    private $name;
    private $id;

    /**
     * Constructor
     */
    public function __construct($name)
    {
        $this->name = $name; // session name
    }

    /**
     * Start session
     * @codeCoverageIgnore
     */
    public function start()
    {
        session_name($this->name); // set session name
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // start session
        }

        $this->session = &$_SESSION[$this->name];

        $this->id = session_id();
        return $this->id;
    }

    /**
     * Reset session
     * @codeCoverageIgnore
     */
    public function reset()
    {
        session_destroy();
        session_start();
        session_regenerate_id(true);

        $this->id = session_id();

        return $this->id;
    }

    /**
     * Destroy session
     * @codeCoverageIgnore
     */
    public function destroy()
    {
        session_destroy();
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->session[] = $value;
        } else {
            $this->session[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->session[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->session[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->session[$offset]) ? $this->session[$offset] : null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }
}
