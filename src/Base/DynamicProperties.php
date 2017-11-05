<?php

namespace Bcismariu\Laravel\Payments\Base;

abstract class DynamicProperties 
{
    protected $attributes = [];

    public function __construct($fields = [])
    {
        foreach ($fields as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->attributes)) {
            $this->attributes[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return null;
    }

}