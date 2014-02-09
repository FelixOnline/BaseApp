<?php
namespace FelixOnline\Core;
/*
 * Base model class
 *
 * Creates dynamic getter functions for model fields
 */
class BaseModel {
	public $fields = array(); // array that holds all the database fields
	protected $class;
	protected $item;
	protected $transformers = array();

	const TRANSFORMER_NONE = 1;
	const TRANSFORMER_NO_HTML = 2;

	function __construct($fields, $item = NULL) {
		$this->class = get_class($this);
		$this->item = $item;

		$this->fields = $fields;

		return $this->fields;
	}

	/*
	 * Create dynamic functions
	 */
	function __call($method,$arguments) {
		$meth = $this->from_camel_case(substr($method,3,strlen($method)-3));
		$verb = substr($method, 0, 3);
		switch($verb) {
			case 'get':
				if(array_key_exists($meth, $this->fields)) {
					return $this->fields[$meth]->getValue();
				}
				throw new \FelixOnline\Exceptions\ModelConfigurationException(
					'The requested field "'.$meth.'" does not exist',
					$verb,
					$meth,
					$this->class,
					$this->item
				);
				break;
			case 'set':
				if (array_key_exists($meth, $this->transformers)) {
					switch($this->transformers[$meth]) {
						case self::TRANSFORMER_NO_HTML:
							$this->fields[$meth]->setValue(strip_tags($arguments[0]));
							break;
						case self::TRANSFORMER_NONE:
						default:
							$this->fields[$meth]->setValue($arguments[0]);
							break;
					}
				} else {
					$this->fields[$meth]->setValue($arguments[0]);
				}

				return $this;
				break;
			case 'has':
				if (array_key_exists($meth, $this->fields)) {
					return true;
				}
				return false;
				break;
			default:
				throw new \FelixOnline\Exceptions\ModelConfigurationException(
					'The requested verb is not valid',
					$verb,
					$meth,
					$this->class,
					$this->item
				);
				break;
		}
	}

	/*
	 * Public: Set field transformers
	 *
	 $ $transformers - array
	 *
	 * Returns transformers
	 */
	public function setTransformers($transformers) {
		$this->transformers = $transformers;
		return $this->transformers;
	}

	/*
	 * Convert camel case to underscore
	 * http://www.paulferrett.com/2009/php-camel-case-functions/
	 */
	function from_camel_case($str) {
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
}
