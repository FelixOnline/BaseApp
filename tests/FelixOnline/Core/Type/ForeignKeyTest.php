<?php

require_once __DIR__ . '/../../../AppTestCase.php';
//require_once __DIR__ . '/../../../utilities.php';

class ForeignKeyTest extends AppTestCase
{
    public $fixtures = array(
        'audit_log'
    );

    public function testGetValue()
    {
        $image = new \FelixOnline\Core\AuditLog(2);

        $key = (new \FelixOnline\Core\Type\ForeignKey('FelixOnline\Core\AuditLog', array()))->setValue(2);

        $this->assertEquals($key->getValue(), $image);
    }

    public function testSetValue()
    {
        $image = new \FelixOnline\Core\AuditLog(2);

        $key = (new \FelixOnline\Core\Type\ForeignKey('FelixOnline\Core\AuditLog', array()))->setValue($image);

        $this->assertEquals($key->getValue(), $image);
    }
}
