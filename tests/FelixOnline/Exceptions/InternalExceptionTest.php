<?php
class InternalExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testException()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'foo'
		);
		throw new \FelixOnline\Exceptions\InternalException('foo');
	}

	public function testExceptionCode()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\InternalException',
			'foo',
			101
		);
		throw new \FelixOnline\Exceptions\InternalException('foo');
	}
}
