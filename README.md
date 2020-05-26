# VanillaForums PHP

## Prerequisites

In order to use this simple file management interface, you need to have the following software packages installed:

- php
- composer (as package or as a static file (PHAR))

In order to be able to run unit tests, you need to have the following additionnal softare packages installed:

- docker-ce
- docker-compose

## Install dependencies

To use this code, launch the following commands.

For a package installation of `composer`, use the following commands:

```bash
# use the code
composer install
# launch tests
composer install --dev
```

For a static file installation of `composer`, use the following commands:

```bash
# use the code
php composer.phar install
# launch tests
php composer.phar install --dev
```

## Examples

In order to manage files on a local filesystem, use the following code:

```php
<?php

require 'vendor/autoload.php';

try {
  $local = new LocalFileSystem();
  // put a new file
  $local->put("path_to_file", "content");
  // get an existing file
  print $local->get("path_to_file").PHP_EOL;
} catch (Exception $e) {
  print $e->getMessage();
}
```

To manage files in Amazon S3, use the following code:

```php
<?php

require 'vendor/autoload.php';

try {
  $s3 = new AmazonS3Service("api_key", "secret_key");
  // put a new file
  $s3->put("bucket_name/file_name", "content");
  // get an existing file
  print $s3->get("bucket_name/file_name").PHP_EOL;
} catch (Exception $e) {
  print $e->getMessage();
}
```

## Launch unit tests

In order to launch tests, use the following commands:

```bash
# launch localstack to mock S3 API
docker-compose up -d
# launch unit tests
./vendor/bin/phpunit tests --testdox
```

## Known issues / improvements

- currently all objects put through S3 interface are created/updated with `public-read` ACL
- it is only possible for both interfaces to deal with text files (both put and get operations)
- unit tests are not implemented with a virtual filesystem mocking module (like `vfsStream`) for `LocalFileSystem` class
- S3 unit tests are not working because of a difference between response from `localstack` S3 API and official S3 API