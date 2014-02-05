<?php

require_once __DIR__ . '/../../AppTestCase.php';

use FelixOnline\Core\Type\CharField;

class BaseModelTest extends AppTestCase
{
	public $fixtures = array(
		'users',
	);

	public function testGetField()
	{
		$model = new \FelixOnline\Core\BaseModel(array(
			'foo' => (new CharField())->setValue('bar')
		));

		$this->assertEquals($model->getFoo(), 'bar');
	}

	public function testGetMissingField()
	{
		$model = new \FelixOnline\Core\BaseModel(array());

		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelConfigurationException',
			'The requested field "foo" does not exist'
		);

		$model->getFoo();
	}

	public function testSetField()
	{
		$model = new \FelixOnline\Core\BaseModel(array(
			'foo' => new CharField()
		));
		$model->setFoo('bar');

		$this->assertEquals($model->getFoo(), 'bar');
	}

	public function testHasField()
	{
		$model = new \FelixOnline\Core\BaseModel(array(
			'bar' => new CharField()
		));

		$this->assertFalse($model->hasFoo());

		$model->setBar('fiz');
		$this->assertTrue($model->hasBar());
	}

	public function testWrongVerb()
	{
		$model = new \FelixOnline\Core\BaseModel(array());
		$this->setExpectedException(
			'FelixOnline\Exceptions\ModelConfigurationException',
			'The requested verb is not valid'
		);
		$model->geeFoo();
	}

	public function testGetFields()
	{
		$fields = array(
			'foo' => (new CharField())->setValue('bar')
		);
		$model = new \FelixOnline\Core\BaseModel($fields);

		$this->assertEquals($fields, $model->getFields());
	}

	public function testFieldFilters()
	{
		$model = new \FelixOnline\Core\BaseModel(array(
			'id' => 1,
			'foo' => 'bar',
			'xxx' => 'bbb',
		));

		$model->setFieldFilters(array(
			'foo' => 'fizz',
			'xxx' => 'yyy',
		));

		$model->setDbtable('test');

		$this->assertEquals(
			$model->constructInsertSQL($model->getFields()),
			"INSERT INTO `test` (`id`, `fizz`, `yyy`) VALUES (1, 'bar', 'bbb')"
		);

		$this->assertEquals(
			$model->constructUpdateSQL(array(
				'foo' => 'bars',
				'xxx' => 'aaa'
			)),
			"UPDATE `test` SET `fizz`='bars', `yyy`='aaa' WHERE `id`=1"
		);
	}
}
