<?php

require 'vendor/autoload.php';

class LocalFileSystem implements GenericStorage {

  public function put($path, $data) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
      throw new Exception('Directory does not exist');
    } elseif (!is_writeable($dir)) {
      throw new Exception('Directory is not writable');
    } else {
      file_put_contents($path, $data);
    }
  }

  public function get($path) {
    if (!file_exists($path)) {
      throw new Exception('File does not exist');
    } elseif (!is_readable($path)) {
      throw new Exception('File is not readable');
    } else {
      return file_get_contents($path);
    }
  }
}