<?php

namespace Blazon\Model;

trait PropertyTrait
{
    protected $properties = [];
    
    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }
    
    public function getProperty($name)
    {
        return $this->properties[$name];
    }
    
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
}
