<?php

function __autoload($class) {
	$parts = explode('\\', $class);
	require 'src/' . implode($parts, "/") . '.php';
}
