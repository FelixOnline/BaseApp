<?php
namespace FelixOnline\Exceptions;

/**
 * Base of all exceptions
 */
class UniversalException extends \Exception {
	const EXCEPTION_UNIVERSAL = 100;
	const EXCEPTION_INTERNAL = 101;
	const EXCEPTION_MODEL_NOTFOUND = 102;
	const EXCEPTION_MODEL = 103;
	const EXCEPTION_ERRORHANDLER = 104;
	const EXCEPTION_SQL = 105;

	protected $user;
	
	public function __construct(
		$message,
		$code = self::EXCEPTION_UNIVERSAL,
		\Exception $previous = null
	) {
		try {
			$app = \FelixOnline\Core\App::getInstance();
			$this->user = $app['currentuser'];
		} catch(\Exception $e) {
			// no app
			$this->user = null;
		}

		parent::__construct($message, $code, $previous);
	}
	
	public function getUser() {
		return $this->user;
	}
}
