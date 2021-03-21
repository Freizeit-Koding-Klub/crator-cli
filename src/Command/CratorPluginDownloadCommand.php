<?php

namespace App\Command;

use App\Services\CratorHandler;
use App\Services\FilePathHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CratorPluginDownloadCommand extends Command
{
    protected static $defaultName = 'crator:plugin:download';

    /**
     * @var CratorHandler
     */
    private $cratorHandler;

    /**
     * @var FilePathHelper
     */
    private $filePathHelper;

    public function __construct(
        CratorHandler $cratorHandler,
        FilePathHelper $filePathHelper
    ) {
        $this->cratorHandler = $cratorHandler;
        $this->filePathHelper = $filePathHelper;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Calls crator plugin api and download the plugin.')
            ->addArgument('pluginId',InputArgument::REQUIRED, 'Plugin ID in crator API. Example input: 1')
            ->addArgument('pathToPlugin',InputArgument::REQUIRED, 'Path to root dir of Plugin. Both absolute and relative allowed. Example: /var/demo/plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $apiUrl = CratorHandler::CRATOR_API_BASE_URL . '/download/' . urlencode($input->getArgument('pluginId'));
            $absTargetPath = $this->filePathHelper->getAbsolutePath($input->getArgument('pathToPlugin'));

            $this->cratorHandler->callCratorAndMergeFiles(
                $apiUrl,
                $absTargetPath
            );
        } catch (\Exception $exception) {
            $output->writeln("Something went wrong. Error message: {$exception->getMessage()}");
            $output->writeln("Trace as string: {$exception->getTraceAsString()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}