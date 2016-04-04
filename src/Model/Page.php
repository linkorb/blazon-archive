<?php

namespace Blazon\Model;

class Page implements PageInterface
{
    protected $name;
    protected $title;
    protected $src;
    protected $handler;
    protected $layout;
    
    public function __construct($name, $src = null)
    {
        $this->setName($name);
        $this->setSrc($src);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getTitle()
    {
        if (!$this->title) {
            return $this->getName();
        }
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function getSrc()
    {
        return $this->src;
    }
    
    public function setSrc($src)
    {
        $this->src = $src;
        return $this;
    }
    
    public function getHandler()
    {
        return $this->handler;
    }
    
    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }
    
    public function getLayout()
    {
        return $this->layout;
    }
    
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
    
    
}
