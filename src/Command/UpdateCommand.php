<?php

namespace App\Command;

use App\Services\VersionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpdateCommand extends Command
{
    const GITHUB_API_RELEASE_URL = 'https://api.github.com/repos/Freizeit-Koding-Klub/crator-cli/releases';

    protected static $defaultName = 'crator:update';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(
        KernelInterface $kernel,
        HttpClientInterface $client
    ) {
        $this->kernel = $kernel;
        $this->client = $client;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Returns the current version of crator-cli.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $currentVersion = VersionHelper::getCurrentVersion();

            $latestRelease = $this->getLatestReleaseFromGithub();
            $latestReleaseVersion = $latestRelease['tag_name'];

            $output->writeln('Latest release version: ' . $latestReleaseVersion);
            $output->writeln('Current release version: ' . $currentVersion);

            if (version_compare($currentVersion, $latestReleaseVersion, '>=')) {
                $output->writeln('Crator-CLI is already up to date!');

                return Command::SUCCESS;
            }

            $output->writeln('Downloading latest release ...');

            $response = $this->client->request(
                'GET',
                $latestRelease['assets'][0]['browser_download_url']
            );

            $newRelease = $response->getContent();

            $output->writeln('Replacing executable file ...');

            list($scriptPath) = get_included_files();
            $result = file_put_contents($scriptPath, $newRelease);

            if ($result === false) {
                throw new \Exception("Could not replace crator-cli file.");
            }else {
                $output->write("Successfully updated crator-cli to version {$latestReleaseVersion}!");
            }

            //we need to die here, becuase the original file was replaced with the latest release. If we don't die here,
            // the script will try to execute missing files and therefore throw errors.
            die();
        } catch (\Exception $exception) {
            $output->writeln("Something went wrong. Error message: {$exception->getMessage()}");
            $output->writeln("Trace as string: {$exception->getTraceAsString()}");

            return Command::FAILURE;
        }
    }

    private function getLatestReleaseFromGithub()
    {
        $response = $this->client->request(
            'GET',
            self::GITHUB_API_RELEASE_URL
        );

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new \Exception("HTTP-Status-Code from Github-API was expected to be 200. Got {$response->getStatusCode()} instead.");
        }

        $content = $response->toArray();

        return $content[0];
    }
}