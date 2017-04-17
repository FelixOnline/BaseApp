<?php
namespace FelixOnline\Base\Type;

class ForeignKey extends BaseType {
    protected $placeholder = "'%s'";
    public $class;

    public function __construct($class, $config = array()) {
        if(!class_exists($class)) {
            throw new \FelixOnline\Exceptions\InternalException('Class ' . $class . ' not found');
        }

        $this->class = $class;
        parent::__construct($config);
    }

    public function getValue() {
        if(!is_null($this->value)) {
            try {
                return new $this->class($this->value);
            } catch(\FelixOnline\Exceptions\ModelNotFoundException $e) {
                $this->value = null;
                return null;
            }
        }
        return null;
    }

    public function setValue($value) {
        if(is_object($value)) {
            $pk = $value->fields[$value->pk];
            $this->value = $pk->getValue();
        } else {
            $this->value = $value;
        }
        return $this;
    }

    public function getSQL() {
        $app = \FelixOnline\Base\App::getInstance();

        if(is_null($this->value) && $this->config['null'] == true) {
            return 'NULL';
        }

        return $app['safesql']->query($this->placeholder, array($this->value));
    }
}
