<?php

namespace App\Services;

class VersionHelper {
    public static function getCurrentVersion(): string
    {
        return '1.0.0';

        //this will be automatically replaced during box compile
        return '@GITVERSION@';
    }
}