<?php

namespace App\Command;

use App\Services\CratorHandler;
use App\Services\FilePathHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CratorCreateCommand extends Command
{
    protected static $defaultName = 'crator:create';

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
        $this->setDescription('Calls crator create api with config string and inserts response into plugin.')
            ->addArgument('elementName',InputArgument::REQUIRED, 'Crator create api element that is called. Example input: cmselement')
            ->addArgument('pathToPlugin',InputArgument::REQUIRED, 'Path to root dir of Plugin. Both absolute and relative allowed. Example: /var/demo/plugin')
            ->addArgument('configurationString',InputArgument::OPTIONAL, 'Configuration string for crator api. JSON format needed.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $apiUrlWithConfig = CratorHandler::CRATOR_API_BASE_URL . '/create';
            $apiUrlWithConfig .= '?elementName=' . urlencode($input->getArgument('elementName'));
            $apiUrlWithConfig .= '&configurationString=' . urlencode($input->getArgument('configurationString'));

            $absTargetPath = $this->filePathHelper->getAbsolutePath($input->getArgument('pathToPlugin'));

            $this->cratorHandler->callCratorAndMergeFiles(
                $apiUrlWithConfig,
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