<?php
class ModelConfigurationExceptionTest extends PHPUnit_Framework_TestCase
{
	public function testException()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelConfigurationException'
		);
		throw new \FelixOnline\Exceptions\ModelConfigurationException(
			'foo',
			'get',
			'bar',
			'test',
			NULL
		);
	}

	public function testExceptionMessage()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelConfigurationException',
			'foo'
		);
		throw new \FelixOnline\Exceptions\ModelConfigurationException(
			'foo',
			'get',
			'bar',
			'test',
			NULL
		);
	}

	public function testExceptionCode()
	{
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelConfigurationException',
			'foo',
			103
		);
		throw new \FelixOnline\Exceptions\ModelConfigurationException(
			'foo',
			'get',
			'bar',
			'test',
			NULL
		);
	}
}
