<?php

require_once __DIR__ . '/../../AppTestCase.php';

class UniversalExceptionTest extends AppTestCase
{
    public function testException()
    {
        $this->setExpectedException(
            'FelixOnline\Exceptions\UniversalException'
        );
        throw new \FelixOnline\Exceptions\UniversalException('foo');
    }

    public function testExceptionMessage()
    {
        $this->setExpectedException(
            'FelixOnline\Exceptions\UniversalException',
            'foo'
        );
        throw new \FelixOnline\Exceptions\UniversalException('foo');
    }

    public function testExceptionCode()
    {
        $this->setExpectedException(
            'FelixOnline\Exceptions\UniversalException',
            'foo',
            100
        );
        throw new \FelixOnline\Exceptions\UniversalException('foo');
    }

    public function testExceptionHasUser()
    {
        $currentUser = \FelixOnline\Base\App::getInstance()['currentuser'];

        $exception = new \FelixOnline\Exceptions\UniversalException('foo');
        $this->assertSame($exception->getUser(), $currentUser);
    }
}
