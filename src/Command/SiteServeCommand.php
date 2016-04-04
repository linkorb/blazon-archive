<?php

namespace Blazon\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Blazon\Blazon;
use Blazon\Model\Site;
use Blazon\Loader\SiteLoader;
use Blazon\Utils;

class SiteServeCommand extends Command
{
    public function configure()
    {
        $this->setName('site:serve')
            ->setDescription('Serve a Blazon site')
            ->addOption(
                'src',
                null,
                InputOption::VALUE_OPTIONAL,
                'Source directory'
            )
            ->addOption(
                'dest',
                null,
                InputOption::VALUE_OPTIONAL,
                'Destination directory'
            )
            ->addOption(
                'port',
                null,
                InputOption::VALUE_OPTIONAL,
                'Port number',
                8080
            )
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $src = getcwd();
        $port = $input->getOption('port');
        
        if ($input->getOption('src')) {
            $src = $input->getOption('src');
        }
        $src = Utils::makePathAbsolute($src);
        
        $dest = $src . '/build';
        if ($input->getOption('dest')) {
            $dest = $input->getOption('dest');
        }
        $dest = Utils::makePathAbsolute($dest);
        
        putenv('BLAZON_SRC='. $src);
        putenv('BLAZON_DEST='. $dest);
        $webroot = __DIR__ . '/../../web';
        
        $cmd = 'php -S 0.0.0.0:' . $port . ' -t ' . $webroot . ' ' . $webroot . '/index.php';
        exec($cmd);
        
        
        $output->writeLn("<comment>Done</comment>");

    }
}
