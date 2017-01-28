<?php
class ModelNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testException()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelNotFoundException'
		);
		throw new \FelixOnline\Exceptions\ModelNotFoundException(
			'foo',
			'abc',
			'def'
		);
	}

	public function testExceptionMessage()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelNotFoundException',
			'foo'
		);
		throw new \FelixOnline\Exceptions\ModelNotFoundException(
			'foo',
			'abc',
			'def'
		);
	}

	public function testExceptionCode()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelNotFoundException',
			'foo',
			102
		);
		throw new \FelixOnline\Exceptions\ModelNotFoundException(
			'foo',
			'abc',
			'def'
		);
	}
}
