<?php

namespace Blazon\Model;

class Page implements PageInterface
{
    protected $name;
    protected $filename;
    protected $handler;
    protected $content;
    protected $next;
    protected $previous;


    use PropertyTrait;

    public function __construct($name, $content = null, $properties = [])
    {
        $this->name = $name;
        $this->content = $content;
        $this->properties = $properties;
    }


    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
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

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function isIndex()
    {
        return $this->getName()=='index';
    }
}
