<?php

namespace Blazon\Model;

class Site
{
    protected $title;
    use PropertyTrait;
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
