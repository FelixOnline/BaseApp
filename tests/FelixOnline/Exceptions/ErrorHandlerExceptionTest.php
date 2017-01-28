<?php
class ErrorHandlerExceptionTest extends PHPUnit_Framework_TestCase {
    public function testException() {
        $this->setExpectedException(
            'FelixOnline\Exceptions\ErrorHandlerException',
            "Test Error",
            104
        );

        \FelixOnline\Exceptions\ErrorHandlerException::errorhandler(
            1,
            "Test Error",
            "test",
            2,
            "test2"
        );
    }

    public function testExceptionErrno() {
        try {
            \FelixOnline\Exceptions\ErrorHandlerException::errorhandler(
                1,
                "Test Error",
                "test",
                2,
                "test2"
            );
        } catch(\Exception $e) {
            $this->assertEquals($e->getErrno(), 1);
        }
    }

    public function testExceptionErrFile() {
        try {
            \FelixOnline\Exceptions\ErrorHandlerException::errorhandler(
                1,
                "Test Error",
                "test",
                2,
                "test2"
            );
        } catch(\Exception $e) {
            $this->assertEquals($e->getErrorFile(), "test");
        }
    }

    public function testExceptionErrLine() {
        try {
            \FelixOnline\Exceptions\ErrorHandlerException::errorhandler(
                1,
                "Test Error",
                "test",
                2,
                "test2"
            );
        } catch(\Exception $e) {
            $this->assertEquals($e->getErrorLine(), 2);
        }
    }

    public function testExceptionContext() {
        try {
            \FelixOnline\Exceptions\ErrorHandlerException::errorhandler(
                1,
                "Test Error",
                "test",
                2,
                "test2"
            );
        } catch(\Exception $e) {
            $this->assertEquals($e->getContext(), "test2");
        }
    }
}
