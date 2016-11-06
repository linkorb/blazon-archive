<?php

namespace Blazon\Handler;

use Blazon\Blazon;
use Blazon\Model\Page;
use Blazon\Model\Site;

class CommandsHandler
{
    protected $blazon;

    public function __construct(Blazon $blazon)
    {
        $this->blazon = $blazon;
    }

    public function init(Page $page)
    {
        $config = $page->getConfig();
        if (!isset($config['classes'])) {
            throw new RuntimeException("Pages with CommandsHandler require an array of `classes`");
        }
    }

    public function generate(Page $page)
    {
        $config = $page->getConfig();
        $commands = [];
        foreach ($config['classes'] as $className) {
            $command = new $className();
            $commands[] = $command;

            $template = $this->blazon->getTwig()->loadTemplate('@Templates/command.html.twig');
            $site = $this->blazon->getSite();

            $data = [
                'site' => $site,
                'page' => $page,
                'command' => $command
            ];

            $output = $template->render($data);

            $filename = $page->getName() . '__' . str_replace(':', '__', $command->getName()) . '.html';
            file_put_contents($this->blazon->getDest() . '/' . $filename, $output);
        }


        $template = $this->blazon->getTwig()->loadTemplate('@Templates/commands.html.twig');
        $site = $this->blazon->getSite();

        $data = [
            'site' => $site,
            'page' => $page,
            'commands' => $commands
        ];

        $output = $template->render($data);

        $filename = $page->getName() . '.html';
        file_put_contents($this->blazon->getDest() . '/' . $filename, $output);

        return;

    }
}
