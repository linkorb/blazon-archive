<?php

namespace Blazon\Handler;

use Blazon\Blazon;
use Blazon\Model\Site;
use Blazon\Model\Page;
use Parsedown;
use VKBansal\FrontMatter\Parser as FrontMatterParser;
use VKBansal\FrontMatter\Document as FrontMatterDocument;

class TwigHandler
{
    protected $blazon;
    
    public function __construct(Blazon $blazon)
    {
        $this->blazon = $blazon;
    }
    
    public function init(Page $page)
    {
    }
    
    public function generate(Page $page)
    {
        $template = $this->blazon->getTwig()->loadTemplate($page->getSrc());
        $site = $this->blazon->getSite();
        
        $data = [
            'site' => $site,
            'page' => $page
        ];
        
        $output = $template->render($data);
        
        file_put_contents($this->blazon->getDest() . '/' . $page->getName() . '.html', $output);
    }
}
