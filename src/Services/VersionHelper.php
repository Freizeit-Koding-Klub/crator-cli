<?php

namespace App\Services;

class VersionHelper {
    public static function getCurrentVersion(): string
    {
        //this will be automatically replaced during box compile
        return '@GITVERSION@';
    }
}