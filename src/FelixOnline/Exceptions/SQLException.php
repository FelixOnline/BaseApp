<?php
namespace FelixOnline\Exceptions;
/**
 * If there is a SQL error
 */
class SQLException extends UniversalException {
    protected $query;

    public function __construct(
        $message,
        $query,
        $code = parent::EXCEPTION_SQL,
        \Exception $previous = null
    ) {
        $this->query = $query;

        parent::__construct($message, $code, $previous);
    }

    public function getQuery() {
        return $this->query;
    }
}
