<?php

namespace FelixOnline\Exceptions;

class GlueRouteException extends UniversalException {
    public function __construct($message, $path, $code = parent::EXCEPTION_GLUE_ROUTE, Exception $previous = null) {
        parent::__construct($message, $path, $code, $previous);
    }
}
