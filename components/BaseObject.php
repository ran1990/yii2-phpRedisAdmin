<?php
/**
 */
namespace life2016\phpredis\components;

class BaseObject extends \yii\base\BaseObject
{
    private $_attributes = [];

    public function __get($name)
    {
        if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        } elseif ($this->hasAttribute($name)) {
            return null;
        }

        $value = parent::__get($name);

        return $value;
    }

    /**
     * Returns a value indicating whether the model has an attribute with the specified name.
     * @param string $name the name of the attribute
     * @return bool whether the model has an attribute with the specified name.
     */
    public function hasAttribute($name)
    {
        return isset($this->_attributes[$name]);
    }

    /**
     * PHP setter magic method.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    public function __isset($name)
    {
        try {
            return $this->__get($name) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->_attributes[$name]);

        }  else {
            parent::__unset($name);
        }
    }




}