<?php
namespace FelixOnline\Base;

interface EnvironmentInterface {
    public function getGlue();
    public function getResponse();
    public function setResponse($response);

    public function dispatch();
    public function emit();
    public function dispatchAndEmit();

    public function terminate($status = 0);
}
