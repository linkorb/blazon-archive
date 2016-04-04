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

class SiteGenerateCommand extends Command
{
    public function configure()
    {
        $this->setName('site:generate')
            ->setDescription('Generate a Blazon site')
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
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $src = getcwd();
        if ($input->getOption('src')) {
            $src = $input->getOption('src');
        }
        $src = Utils::makePathAbsolute($src);
        
        $dest = $src . '/build';
        if ($input->getOption('dest')) {
            $dest = $input->getOption('dest');
        }
        $dest = Utils::makePathAbsolute($dest);
        
        $output->writeLn('<info>Generating site</info>');
        $output->writeLn('   * Source: ' . $src);
        $output->writeLn('   * Destination: ' . $dest);
        
        $blazon = new Blazon($src, $dest, $output);
        $blazon->run();
        
        $output->writeLn("<comment>Done</comment>");

    }
}
