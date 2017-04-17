<?php
namespace FelixOnline\Exceptions;

/**
 * If there is an error in the model (i.e. wrong verb)
 */
class ModelConfigurationException extends UniversalException
{
    protected $verb;
    protected $property;
    protected $class;
    protected $item;

    public function __construct(
        $message,
        $verb,
        $property,
        $class,
        $item,
        $code = parent::EXCEPTION_MODEL,
        \Exception $previous = null
    ) {
        $this->verb = $verb;
        $this->property = $property;
        $this->class = $class;
        $this->item = $item;

        parent::__construct($message, $code, $previous);
    }

    public function getVerb()
    {
        return $this->verb;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getItem()
    {
        return $this->item;
    }
}
