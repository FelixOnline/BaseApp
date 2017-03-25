<?php
namespace FelixOnline\Exceptions;
/**
 * For if a model does not exist in the database
 */
class ModelNotFoundException extends UniversalException {
    protected $class;
    protected $item;

    public function __construct(
        $message,
        $class,
        $item = null,
        $code = parent::EXCEPTION_MODEL_NOTFOUND,
        \Exception $previous = null
    ) {
        $this->class = $class;
        $this->item = $item;

        parent::__construct($message, $code, $previous);
    }

    public function getClass() {
        return $this->class;
    }

    public function getItem() {
        return $this->item;
    }
}
