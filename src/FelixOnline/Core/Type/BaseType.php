<?php
namespace FelixOnline\Core\Type;

class BaseType
{
	public $config;
	protected $value;
	protected $placeholder = "'%s'";

	const TRANSFORMER_NONE = 1;
	const TRANSFORMER_NO_HTML = 2;

	public function __construct($config = array())
	{
		$this->config = $config + array(
			'primary' => false,
			'null' => true,
			'transformers' => array(),
		);
	}

	public function setValue($value)
	{
		foreach ($this->config['transformers'] as $transformer) {
			switch ($transformer) {
				case self::TRANSFORMER_NO_HTML:
					$value = strip_tags($value);
					break;
				case self::TRANSFORMER_NONE:
				default:
					break;
			}
		}

		$this->value = $value;
		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getSQL()
	{
		$app = \FelixOnline\Core\App::getInstance();

		if (is_null($this->value) && $this->config['null'] == true) {
			return 'NULL';
		}

		return $app['safesql']->query($this->placeholder, array($this->value));
	}
}
