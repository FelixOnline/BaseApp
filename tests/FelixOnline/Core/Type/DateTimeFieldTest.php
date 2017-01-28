<?php

require_once __DIR__ . '/../../../AppTestCase.php';
//require_once __DIR__ . '/../../../utilities.php';

class DateTimeFieldTest extends AppTestCase
{
	public function testSetDateString()
	{
		$datetime = "2012-11-09 13:26:39";
		$field = new \FelixOnline\Core\Type\DateTimeField();
		$field->setValue($datetime);

		$this->assertEquals($field->getValue(), strtotime($datetime));
	}

	public function testSetDateTimestamp()
	{
		$datetime = "2012-11-09 13:26:39";
		$field = new \FelixOnline\Core\Type\DateTimeField();
		$field->setValue(strtotime($datetime));

		$this->assertEquals($field->getValue(), strtotime($datetime));
	}

	public function testSetDateException()
	{
		$this->setExpectedException('FelixOnline\Exceptions\InternalException', 'Invalid date');

		$field = new \FelixOnline\Core\Type\DateTimeField();
		$field->setValue('foo');
	}

	public function testGetSQL()
	{
		$datetime = "2012-11-09 13:26:39";
		$field = new \FelixOnline\Core\Type\DateTimeField();
		$field->setValue($datetime);

		$this->assertEquals($field->getSQL(), "'" . $datetime . "'");
	}
}
