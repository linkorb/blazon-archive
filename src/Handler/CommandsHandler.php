<?php

namespace Blazon\Handler;

use ReflectionClass;
use ReflectionException;
use RuntimeException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;

use Blazon\Blazon;
use Blazon\Model\Page;
use Blazon\Model\Site;

class CommandsHandler
{
    const CONF_CHILDNAME_STRATEGY = 'child_page_name_strategy';
    const CHILDNAME_STRATEGY_PREFIX = 'prefix_with_parent_name';
    const CHILDNAME_STRATEGY_NOPREFIX = 'no_prefix';

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
        $outputPath = $page->getBaseDir() . DIRECTORY_SEPARATOR;

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
                'command' => $command,
                'index_page' => $page, /* use this instead of page */
                'index_page_url' => $pageOutputName,
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
        $conf = $parentPage->getConfig();
        $strategy = self::CHILDNAME_STRATEGY_PREFIX;

        if (array_key_exists(self::CONF_CHILDNAME_STRATEGY, $conf)) {
            $strategy =  $conf[self::CONF_CHILDNAME_STRATEGY];
        }

        if ($strategy === self::CHILDNAME_STRATEGY_PREFIX) {
            return sprintf(
                '%s__%s.html',
                $parentPage->getName(),
                str_replace(':', '__', $command->getName())
            );
        } else {
            return sprintf('%s.html', str_replace(':', '__', $command->getName()));
        }
    }

    /*
     * Load a Symfony Console Command instance.
     *
     * We desire to obtain the Command configuration without being required to
     * inject the Command's dependencies.  Here, we use Reflection to obtain an
     * instance of a Command, bypassing its constructor.
     */
    private function loadCommand($className, $pageName)
    {
        $command = null;
        $method_configure = null;

        try {

            $refl = new ReflectionClass($className);
            if (! $refl->isSubclassOf(Command::class)) {
                throw new RuntimeException(
                    sprintf(
                        'Cannot generate page "%s" because of an error in `classes`: The class "%s" is not a Command.',
                        $pageName,
                        $className
                    )
                );
            }
            $command = $refl->newInstanceWithoutConstructor();
            $method_configure = $refl->getMethod('configure');

        } catch (ReflectionException $e) {

            throw new RuntimeException(
                sprintf(
                    'Cannot generate page "%s" because of an error in `classes`.',
                    $pageName
                ),
                null,
                $e
            );

        }

        # Command.__construct creates an InputDefinition ...
        $command->setDefinition(new InputDefinition);

        # ... and calls the configure method
        if (! $method_configure->isPublic()) {
            $method_configure->setAccessible(true);
        }
        $method_configure->invoke($command);

        return $command;
    }
}
