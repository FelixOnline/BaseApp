<?php
namespace FelixOnline\Base\Type;

class DateTimeField extends BaseType
{
    protected $placeholder = "'%s'";

    public function setValue($value)
    {
        if (is_string($value)) {
            try {
                $this->value = (new \DateTime($value))->getTimestamp();
                $this->raw_value = $value;
            } catch (\Exception $e) {
                throw new \FelixOnline\Exceptions\InternalException('Invalid date');
            }
        } elseif (is_int($value)) {
            $this->value = $value;
            $this->raw_value = (new \DateTime("@$value"))->format('Y-m-d H:i:s');
        } elseif (is_null($value)) {
            $this->value = $value;
            $this->raw_value = $value;
        } else {
            throw new \FelixOnline\Exceptions\InternalException('Invalid date');
        }

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getRawValue()
    {
        return $this->raw_value;
    }

    public function getSQL()
    {
        $app = \FelixOnline\Base\App::getInstance();

        if (is_null($this->value) && $this->config['null'] == true) {
            return 'NULL';
        }

        $datetime = (new \DateTime("@$this->value"))->format('Y-m-d H:i:s');

        return $app['safesql']->query($this->placeholder, array($datetime));
    }
}
