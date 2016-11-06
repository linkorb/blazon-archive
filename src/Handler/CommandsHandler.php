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
        $site = $this->blazon->getSite();
        $config = $page->getConfig();
        $outputPath = $this->blazon->getDest() . DIRECTORY_SEPARATOR;

        $pageName = $page->getName();
        $pageOutputName = $pageName . '.html';

        $commandTemplate = $this->blazon->getTwig()->loadTemplate('@Templates/command.html.twig');
        $pageTemplate = $this->blazon->getTwig()->loadTemplate('@Templates/commands.html.twig');

        $commands = [];
        foreach ($config['classes'] as $className) {
            $command = $this->loadCommand($className, $pageName);
            $outputName = $this->cmdNameToResourceName($command, $page);
            $commands[$outputName] = $command;
        }
        foreach ($commands as $outputName => $command) {
            $data = [
                'site' => $site,
                'page' => $page,
                'command' => $command
            ];
            $output = $commandTemplate->render($data);
            file_put_contents($outputPath . $outputName, $output);
        }

        $data = [
            'site' => $site,
            'page' => $page,
            'commands' => $commands
        ];
        $output = $pageTemplate->render($data);
        file_put_contents($outputPath . $pageOutputName, $output);

        return;

    }

    /*
     * Convert a command name to an HTML file name.
     */
    protected function cmdNameToResourceName(Command $command, Page $parentPage)
    {
        return sprintf(
            '%s__%s.html',
            $parentPage->getName(),
            str_replace(':', '__', $command->getName())
        );
    }

    /*
     * Load a Symfony Console Command instance.
     */
    private function loadCommand($className, $pageName)
    {
        $command = new $className();
        return $command;
    }
}
