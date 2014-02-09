<?php

require_once __DIR__ . '/../../../AppTestCase.php';
require_once __DIR__ . '/../../../utilities.php';

class BaseTypeTest extends AppTestCase
{
	public function testNoHTMLTransformer()
	{
		$field = new \FelixOnline\Core\Type\BaseType(array(
			'transformers' => array(
				\FelixOnline\Core\Type\BaseType::TRANSFORMER_NO_HTML
			)
		));

		$html = "<h1>Hello world</h1>";

		$field->setValue($html);
		$this->assertEquals($field->getValue(), "Hello world");
	}
}
