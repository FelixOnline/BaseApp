<?php

require_once __DIR__ . '/../../AppTestCase.php';

use FelixOnline\Base\Type\CharField;

class BaseModelTest extends AppTestCase
{
    public $fixtures = array(
        'users',
        'audit_log'
    );

    public function testGetField()
    {
        $model = new \FelixOnline\Base\BaseModel(array(
            'foo' => (new CharField())->setValue('bar')
        ));

        $this->assertEquals($model->getFoo(), 'bar');
    }

    public function testGetMissingField()
    {
        $model = new \FelixOnline\Base\BaseModel(array());

        $this->setExpectedException(
            'FelixOnline\Exceptions\ModelConfigurationException',
            'The requested field "foo" does not exist'
        );

        $model->getFoo();
    }

    public function testSetField()
    {
        $model = new \FelixOnline\Base\BaseModel(array(
            'foo' => new CharField()
        ));
        $model->setFoo('bar');

        $this->assertEquals($model->getFoo(), 'bar');
    }

    public function testHasField()
    {
        $model = new \FelixOnline\Base\BaseModel(array(
            'bar' => new CharField()
        ));

        $this->assertFalse($model->hasFoo());

        $model->setBar('fiz');
        $this->assertTrue($model->hasBar());
    }

    public function testWrongVerb()
    {
        $model = new \FelixOnline\Base\BaseModel(array());
        $this->setExpectedException(
            'FelixOnline\Exceptions\ModelConfigurationException',
            'The requested verb is not valid'
        );
        $model->geeFoo();
    }
}
