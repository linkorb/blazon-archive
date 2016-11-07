<?php

namespace Blazon\Handler;

use Blazon\Blazon;
use Blazon\Model\Page;
use Blazon\Model\Site;

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

        file_put_contents($page->getBaseDir() . '/' . $page->getName() . '.html', $output);
    }
}
