<?php

namespace Blazon\Handler;

use Blazon\Blazon;
use Blazon\Model\Site;
use Blazon\Model\Page;
use Parsedown;
use VKBansal\FrontMatter\Parser as FrontMatterParser;
use VKBansal\FrontMatter\Document as FrontMatterDocument;

class MarkdownHandler
{
    protected $blazon;
    protected $content;
    
    public function __construct(Blazon $blazon)
    {
        $this->blazon = $blazon;
    }
    
    public function init(Page $page)
    {
        $data = file_get_contents($this->blazon->getSrc() . '/' . $page->getSrc());

        $doc = FrontMatterParser::parse($data);
        $config = $doc->getConfig();
        foreach ($config as $key => $value) {
            $page->setProperty($key, $value);
        }
        /*
        if (isset($config['layout'])) {
            $page->setLayout($config['layout']);
        }
        */
        $this->content = $doc->getContent();
    }
    
    public function generate(Page $page)
    {
        $parsedown = new Parsedown();
        $html = $parsedown->text($this->content);
        
        $layout = $page->getLayout();
        if (!$layout) {
            $layout = 'default';
        }
        $template = $this->blazon->getTwig()->loadTemplate('templates/' . $layout . '.html.twig');
        $site = $this->blazon->getSite();
        
        $data = [
            'content' => $html,
            'site' => $site,
            'page' => $page
        ];
        
        $output = $template->render($data);
        
        file_put_contents($this->blazon->getDest() . '/' . $page->getName() . '.html', $output);
    }
}
