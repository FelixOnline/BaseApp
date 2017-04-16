<?php

namespace FelixOnline\Exceptions;

// Glue exceptions - our fault
class GlueInternalException extends UniversalException {
	protected $class;
	protected $method;
	protected $url;

	public function __construct($message, $url, $class, $method, $code = parent::EXCEPTION_GLUE, Exception $previous = null) {
		$this->class = $class;
		$this->url = $url;
		$this->method = $method;
		parent::__construct($message, $code, $previous);
	}

	public function getClass() {
		return $this->class;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getUrl() {
		return $this->url;
	}
}
