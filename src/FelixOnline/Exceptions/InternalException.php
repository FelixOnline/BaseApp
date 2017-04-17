<?php
namespace FelixOnline\Exceptions;

/**
 * Generic - our fault
 */
class InternalException extends UniversalException
{
    public function __construct(
        $message,
        $code = parent::EXCEPTION_INTERNAL,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
