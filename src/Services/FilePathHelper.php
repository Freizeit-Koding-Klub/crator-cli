<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class FilePathHelper {

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(
        KernelInterface $kernel
    ) {
        $this->kernel = $kernel;
        $this->fileSystem = new Filesystem();
    }

    public function getAbsolutePath(string $pluginPath): string
    {
        if (empty($pluginPath)) {
            throw new \Exception("No path provided!");
        }

        if ($this->fileSystem->isAbsolutePath($pluginPath)) {
            $absPluginPath = $pluginPath;
        } else {
            $absPluginPath = getcwd() . '/' . $pluginPath;
        }

        return $absPluginPath;
    }
}