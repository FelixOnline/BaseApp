<?php

require_once __DIR__ . '/../../../AppTestCase.php';
//require_once __DIR__ . '/../../../utilities.php';

class ForeignKeyTest extends AppTestCase
{
	public $fixtures = array(
		'images',
		'users',
	);

	public function testGetValue()
	{
		$image = new \FelixOnline\Core\Image(23);

		$key = (new \FelixOnline\Core\Type\ForeignKey('FelixOnline\Core\Image', array()))->setValue(23);

		$this->assertEquals($key->getValue(), $image);
	}

	public function testSetValue()
	{
		$image = new \FelixOnline\Core\Image(23);

		$key = (new \FelixOnline\Core\Type\ForeignKey('FelixOnline\Core\Image', array()))->setValue($image);

		$this->assertEquals($key->getValue(), $image);
	}
}
