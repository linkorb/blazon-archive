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
    protected $site;
    protected $filename;
    
    public function __construct($filename, OutputInterface $output = null, $dest = null)
    {
        $this->filename = $filename;
        if (!file_exists($this->filename)) {
            throw new RuntimeException("blazon.yml file not found: " . $this->filename);
        }

        if (!$output) {
            $output = new \Symfony\Component\Console\Output\NullOutput();
        }
        $this->output = $output;
        
        $this->src = dirname($filename);
        $this->dest = $dest;
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
    
    public function getOutput()
    {
        return $this->output;
    }
    
    protected $pages = [];
    
    public function addPage(Page $page)
    {
        $this->pages[$page->getName()] = $page;
    }
    
    public function getSite()
    {
        return $this->site;
    }
    
    public function getPages()
    {
        return $this->pages;
    }

    public function load()
    {
        $this->site = new Site();
        $parser = new YamlParser();
        $config = $parser->parse(file_get_contents($this->src . '/blazon.yml'));

        if (!$this->dest) {
            if (isset($config['dest'])) {
                $dest = rtrim($config['dest'], '/');
                if ($dest[0]!='/') {
                    $this->dest = $this->src . '/' . $dest;
                } else {
                    $this->dest = $dest;
                }
            }
        
            if (!$this->dest) {
                $this->dest = dirname($this->filename) . '/build';
            }
        }

        if (isset($config['src'])) {
            $src = rtrim($config['src'], '/');
            if ($src[0]!='/') {
                $this->src .= '/' . $src;
            } else {
                $this->src = $src;
            }
        }
        
        if (isset($config['site']['properties'])) {
            foreach ($config['site']['properties'] as $key => $value) {
                $this->site->setProperty($key, $value);
            }
        }
        
        if (isset($config['pages'])) {
            foreach ($config['pages'] as $name => $pageNode) {
                $page = new Page($name, $pageNode);
                
                $handler = null;
                    
                if (isset($pageNode['src'])) {
                    $pageSrc = $this->src . '/' . $pageNode['src'];
                    if (!file_exists($pageSrc)) {
                        throw new RuntimeException("Page src does not exist: " . $pageSrc);
                    }
                    $page->setSrc($pageNode['src']);
                }
                if (isset($pageNode['handler'])) {
                    $handlerClassName = $pageNode['handler'];
                    $handler = new $handlerClassName($this);
                }
                
                if (!$handler) {
                    if ($page->getSrc()=='') {
                        throw new RuntimeException("No handler and no src specified for page: " . $page->getName());
                    }
                    // guess handler based on file-extension
                    if (substr($pageSrc, -3)=='.md') {
                        $handler = new \Blazon\Handler\MarkdownHandler($this);
                    }
                    if (substr($pageSrc, -5)=='.twig') {
                        $handler = new \Blazon\Handler\TwigHandler($this);
                    }
                    if (substr($pageSrc, -5)=='.html') {
                        $handler = new \Blazon\Handler\HtmlHandler($this);
                    }
                }
                if (!$handler) {
                    throw new RuntimeException("No handler for src file of page " . $page->getName());
                }
                $page->setHandler($handler);
                $handler->init($page, $config);
            

                if (isset($pageNode['properties'])) {
                    foreach ($pageNode['properties'] as $key => $value) {
                        $page->setProperty($key, $value);
                    }
                }

                $this->addPage($page);
            }
        }
        
        
        if (!file_exists($this->src)) {
            throw new RuntimeException("Source directory does not exist: " . $this->src);
        }
        if (!file_exists($this->dest)) {
            throw new RuntimeException("Destination directory does not exist: " . $this->dest);
        }
        
        $loader = new \Twig_Loader_Filesystem($this->src);
        
        $loader->addPath(
            $this->src . '/templates',
            'Templates'
        );
        
        $this->twig = new \Twig_Environment($loader, []);
        
        $filter = new \Twig_SimpleFilter('urlsafe_command_name', function (\Twig_Environment $env, $string) {
            // get the current charset for instance
            $string = str_replace(':', '__', $string);
            return $string;
        }, array('needs_environment' => true));
        $this->twig->addFilter($filter);

        return $this;
    }
    
    public function copyAssets($src, $dest)
    {
        if (!file_exists($dest)) {
            mkdir($dest, 0755);
        }
        $less = new \lessc;
        $this->output->writeLn("Copying assets from $src to $dest");
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $src,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $path = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                $this->output->writeLn(" * Directory: " . $iterator->getSubPathName());
                if (!file_exists($path)) {
                    mkdir($path);
                }
            } else {
                $srcFile = $item;
                $destFile = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                $this->output->writeLn(" * File: " . $iterator->getSubPathName());
                $ext = pathinfo($iterator->getSubPathName(), PATHINFO_EXTENSION);
                switch ($ext) {
                    case 'less':
                        $content = file_get_contents($srcFile);
                        $css = $less->compile($content);
                        $destFile = str_replace('.less', '.css', $destFile);
                        file_put_contents($destFile, $css);
                        break;
                    default:
                        copy($srcFile, $destFile);
                }
            }
        }
    }
    
    public function generate()
    {
        $this->output->writeLn('<info>Generating site</info>');
        $this->output->writeLn('   * Filename: ' . $this->filename);
        $this->output->writeLn('   * src: ' . $this->src);
        $this->output->writeLn('   * dest: ' . $this->dest);
        $this->copyAssets($this->src . '/assets', $this->dest . '/assets');
        
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
