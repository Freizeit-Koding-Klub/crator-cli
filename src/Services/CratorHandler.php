<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use \Symfony\Contracts\HttpClient\ResponseInterface;

class CratorHandler {

    const CRATOR_API_BASE_URL = 'https://crator.emzserver.de';

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(
        HttpClientInterface $client
    ) {
        $this->client = $client;
        $this->fileSystem = new Filesystem();
    }

    public function callCratorAndMergeFiles($url, $absPluginPath)
    {
        if (empty($url) || empty($absPluginPath)) {
            throw new \Exception("URL or plugin path missing.");
        }

        $response = $this->getAndValidateResponseFromApi($url);
        $zipContent = $this->getZipContentFromResponse($response);
        $pathToZipFile = $this->saveZipFile($zipContent, $this->getFilenameFromResponse($response));
        $pathToFolderFromZipFile = $this->extractZipFile($pathToZipFile);

        $this->mergeZipFolderContentIntoTargetFolder(
            $pathToFolderFromZipFile,
            $absPluginPath
        );
    }

    private function getAndValidateResponseFromApi(string $url): ResponseInterface
    {
        $response = $this->client->request(
            'GET',
            $url
        );

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new \Exception("HTTP-Status-Code from Crator-API was expected to be 200. Got {$response->getStatusCode()} instead.");
        }

        return $response;
    }

    private function getZipContentFromResponse(ResponseInterface $response): string
    {
        $zipContent = $response->getContent();

        if (empty($zipContent)) {
            throw new \Exception("Got no file from from Crator-API.");
        }

        return $zipContent;
    }

    private function saveZipFile(string $zipContent, string $filename): string
    {
        $filePath = sys_get_temp_dir() . '/' . uniqid() . $filename;

        $fileSavedSuccess = file_put_contents($filePath, $zipContent);

        if ($fileSavedSuccess === false) {
            throw new \Exception("Could not save zip file.");
        }

        return $filePath;
    }

    private function getFilenameFromResponse(ResponseInterface $response): string
    {
        $arr = explode('filename=', array_shift($response->getHeaders()['content-disposition']));
        $filename = $arr[1];

        if (empty($filename)) {
            throw new \Exception("Cannot get filename from response");
        }

        return $filename;
    }

    private function extractZipFile(string $pathToZipFile): string
    {
        $zip = new \ZipArchive();
        $res = $zip->open($pathToZipFile);

        if ($res !== true) {
            throw new \Exception("Could not read zip archive. Got error code: {$res}");
        }

        $pathToFolderFromZipFile = str_replace('.zip', '', $pathToZipFile);

        $res2 = $zip->extractTo($pathToFolderFromZipFile);

        if ($res2 !== true) {
            throw new \Exception("Could not extract zip archive to folder. Got error code: {$res2}");
        }

        $zip->close();

        return $pathToFolderFromZipFile;
    }

    private function mergeZipFolderContentIntoTargetFolder(string $pathToFolderFromZipFile, $absPluginPath): void
    {
        $rri = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathToFolderFromZipFile));

        foreach ($rri as $file) {
            if ($file->isDir()){
                continue;
            }

            $newPath = $absPluginPath . '/' . str_replace($pathToFolderFromZipFile, '', $file);

            if ($this->fileSystem->exists([$newPath])) {
                $this->fileSystem->appendToFile($newPath, "\r\n\r\n" . file_get_contents($file));
            } else {
                $this->fileSystem->copy($file, $newPath);
            }
        }
    }
}