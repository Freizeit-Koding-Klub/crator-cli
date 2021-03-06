<?php

namespace App\Command;

use App\Services\VersionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected static $defaultName = 'crator:version';

    protected function configure()
    {
        $this->setDescription('Returns the current version of crator-cli.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln("Crator-CLI version: " . VersionHelper::getCurrentVersion());
        } catch (\Exception $exception) {
            $output->writeln("Something went wrong. Error message: {$exception->getMessage()}");
            $output->writeln("Trace as string: {$exception->getTraceAsString()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}