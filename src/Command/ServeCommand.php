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

class ServeCommand extends Command
{
    public function configure()
    {
        $this->setName('serve')
            ->setDescription('Serve a Blazon site')
            ->addOption(
                'filename',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filename'
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
        $port = $input->getOption('port');

        $filename = getcwd() . '/blazon.yml';
        if ($input->getOption('filename')) {
            $filename = $input->getOption('filename');
        }
        
        putenv('BLAZON_FILE='. $filename);
        
        $webroot = __DIR__ . '/../../web';
        
        $cmd = 'php -S 0.0.0.0:' . $port . ' -t ' . $webroot . ' ' . $webroot . '/index.php';
        exec($cmd);
        
        
        $output->writeLn("<comment>Done</comment>");

    }
}
