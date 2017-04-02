<?php
namespace FelixOnline\Exceptions;
/**
 * DB could not connect
 */
class DBConnectionException extends UniversalException {
    public function __construct(
        $message,
        $code = parent::EXCEPTION_DBCONNECT,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
