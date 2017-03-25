<?php
namespace FelixOnline\Core;

interface EnvironmentInterface {
    public function startBuffer();
    public function stopBuffer();
    public function flushBuffer();

    public function exit();
}
