<?php

require_once __DIR__ . '/../../AppTestCase.php';

class SQLExceptionTest extends AppTestCase
{
    public function testException() {
        $this->setExpectedException(
            'FelixOnline\Exceptions\SQLException',
            'Error 1'
        );
        throw new \FelixOnline\Exceptions\SQLException("Error 1", "Error 2");
    }

    public function testExceptionCode() {
        $this->setExpectedException(
            'FelixOnline\Exceptions\SQLException',
            'Error 1',
            105
        );
        throw new \FelixOnline\Exceptions\SQLException("Error 1", "Error 2");
    }

    public function testExceptionQuery() {
        $exception2 = new \FelixOnline\Exceptions\SQLException(
            "Error 1",
            "Error 2"
        );

        $this->assertEquals($exception2->getQuery(), "Error 2");
    }
}
