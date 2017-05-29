<?php
namespace FelixOnline\Base;
use \FelixOnline\Exceptions\InternalException;

/**
 * Session class
 */
class Session implements \ArrayAccess
{
    private $name;
    private $id;
    private $cli;
    private $clistore = array();

    /**
     * Constructor
     */
    public function __construct($name, $cli = false)
    {
        $this->name = $name; // session name
        $this->cli = $cli;
    }

    /**
     * Start session
     * @codeCoverageIgnore
     */
    public function start()
    {
        if(!$this->cli) {
            if (session_status() === PHP_SESSION_DISABLED) {
                throw new InternalException('Sessions are disabled');
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                throw new InternalException('Session already started.');
            }

            session_name($this->name); // set session name
            session_start();

            $this->id = session_id();
        } else {
            $this->clistore = array();
            $this->id = rand();
        }
    }

    /**
     * Reset session
     * @codeCoverageIgnore
     */
    public function reset()
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            session_destroy();
        }

        $this->start();
    }

    /**
     * Emit session data
     * @codeCoverageIgnore
     */
    public function close()
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            session_write_close();
        }

        $this->id = null;
    }

    /**
     * Destroy session
     * @codeCoverageIgnore
     */
    public function destroy()
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            session_destroy();
        }

        $this->id = null;
    }

    public function offsetSet($offset, $value)
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            if (is_null($offset)) {
                $_SESSION[] = $value;
            } else {
                $_SESSION[$offset] = $value;
            }
        } else {
            if (is_null($offset)) {
                $this->clistore[] = $value;
            } else {
                $this->clistore[$offset] = $value;
            }
        }
    }

    public function offsetExists($offset)
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            return isset($_SESSION[$offset]);
        } else {
            return isset($this->clistore[$offset]);
        }
    }

    public function offsetUnset($offset)
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            unset($_SESSION[$offset]);
        } else {
            unset($this->clistore[$offset]);
        }
    }

    public function offsetGet($offset)
    {
        if($this->id == null) {
            throw new InternalException('Session not loaded');
        }

        if(!$this->cli) {
            return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
        } else {
            return isset($this->clistore[$offset]) ? $this->clistore[$offset] : null;
        }
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
