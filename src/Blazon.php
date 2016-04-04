<?php

namespace Blazon;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser as YamlParser;
use Blazon\Model\Site;
use Blazon\Model\Page;
use RuntimeException;

class Blazon
{
    protected $output;
    protected $src;
    protected $dest;
    protected $twig;
    
    public function __construct($src, $dest, OutputInterface $output = null)
    {
        if (!$output) {
            $output = new \Symfony\Component\Console\Output\NullOutput();
        }
        $this->output = $output;
        $this->src = $src;
        $this->dest = $dest;

        if (!file_exists($src)) {
            throw new RuntimeException("Source directory does not exist: " . $src);
        }
        if (!file_exists($src . '/blazon.yml')) {
            throw new RuntimeException("Source directory does not contain a blazon.yml file: " . $src);
        }
        
        if (!file_exists($dest)) {
            throw new RuntimeException("Destination directory does not exist: " . $dest);
        }
        
        $loader = new \Twig_Loader_Filesystem($this->src);
        $this->twig = new \Twig_Environment($loader, []);
    }
    
    public function getSrc()
    {
        return $this->src;
    }
    
    public function getDest()
    {
        return $this->dest;
    }
    
    public function getTwig()
    {
        return $this->twig;
    }
    
    public function getSite()
    {
        return $this->site;
    }
    
    public function getOutput()
    {
        return $this->output;
    }
    
    protected $pages = [];
    
    public function addPage(Page $page)
    {
        $this->pages[$page->getName()] = $page;
    }
    
    public function getPages()
    {
        return $this->pages;
    }

    public function load()
    {
        $parser = new YamlParser();
        $config = $parser->parse(file_get_contents($this->src . '/blazon.yml'));
        
        if (isset($config['pages'])) {
            foreach ($config['pages'] as $name => $pageNode) {
                $page = new Page($name);
                if ($pageNode['src']) {
                    $pageSrc = $this->src . '/' . $pageNode['src'];
                    if (!file_exists($pageSrc)) {
                        throw new RuntimeException("Page src does not exist: " . $pageSrc);
                    }
                    $page->setSrc($pageSrc);
                    if (substr($pageSrc, -3)=='.md') {
                        $handler = new \Blazon\Handler\MarkdownHandler($this);
                        $page->setHandler($handler);
                        $handler->init($page);
                    }
                }
                $this->addPage($page);
            }
        }
        
        /*
        print_r($config);
        print_r($site);
        exit();
        */
        
        return $this;
    }
    
    public function generate()
    {
        foreach ($this->getPages() as $page) {
            $this->output->writeLn('Page: ' . $page->getName());
            $handler = $page->getHandler($this);
            $handler->generate($page);
        }
    }
    
    public function run()
    {
        $this->load();
        $this->generate();
    }
}
