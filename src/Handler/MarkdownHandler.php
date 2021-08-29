<?php

namespace Blazon\Handler;

use Parsedown;
use Webuni\FrontMatter\FrontMatter;

use Blazon\Blazon;
use Blazon\Model\Page;
use Blazon\Model\Site;

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

        $frontMatter = new FrontMatter();
        $doc = $frontMatter->parse($data);
        $data = $doc->getData();
        foreach ($data as $key => $value) {
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

        file_put_contents($page->getBaseDir() . '/' . $page->getName() . '.html', $output);
    }
}
