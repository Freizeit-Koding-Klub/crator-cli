# Crator CLI

## Usage

- Install by downloading the lastest release
- Make the file globally executable by running
    - "mv cratorcli /usr/local/bin/cratorcli"
    - chmod +x /usr/local/bin/cratorcli
- Test by running "cratorcli"

## crator:api
- use this command to fetch a zip-file from the crator api and merge its content into the provided plugin.

## crator:update
- use this command to update to the lastest cratorcli version.

## crator:version
- shows the current cratorcli version.

## Installation for LDE

Get all dependencies by running "composer install" in the project's root directory.

## Creating the phar file

### Environment
Before creating the phar file, make sure that the envorinment is set to prod in the .env file. If the environment is set to dev, symfony will try to write the dev cache when using the phar file, which results in an error, as the phar file is read-only.

### Tool
Use [box](https://github.com/box-project/box) for building the phar file. Install the package as described there.

### Build process
To build the phar file, use "box compile" in the root directory of the project. The phar file is then created in the root directory with the filename "cratorcli".

### Creating a new release
When adding a new feature or something, firstly, commit/merge all changes to master. Then go to github.com and draft a new release with a new release-version. Upload the builded cratorcli file there, so that it can be accessed via the update command.