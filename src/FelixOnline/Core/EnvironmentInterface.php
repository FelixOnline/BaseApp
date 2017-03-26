<?php
namespace FelixOnline\Core;

interface EnvironmentInterface {
    public function getGlue();
    public function getResponse();
    public function setResponse($response);

    public function dispatch();
    public function emit();
    public function dispatchAndEmit();

    public function exit();
}
