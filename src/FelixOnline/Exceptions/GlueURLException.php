<?php

namespace FelixOnline\Exceptions;

class GlueURLException extends UniversalException {
    public function __construct($message, $url, $code = parent::EXCEPTION_GLUE_URL, Exception $previous = null) {
        parent::__construct($message, $url, $code, $previous);
    }
}
