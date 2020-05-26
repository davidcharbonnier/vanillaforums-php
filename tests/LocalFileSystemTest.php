<?php

require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class LocalFileSystemTest extends TestCase {

  public function clean() {
    // delete created files as part of testing
    if (file_exists('/tmp/test.txt')) {
      unlink('/tmp/test.txt');
    }
  }

  public function setUp(): void {
    $this->clean();
  }

  public function tearDown(): void {
    $this->clean();
  }

  public function testCanPutFile(): void {
    $localFileSystem = new LocalFileSystem();
    $localFileSystem->put('/tmp/test.txt', 'test');
    $this->assertFileExists('/tmp/test.txt');
  }

  public function testCannotPutFileInNonExistingDirectory(): void {
    $localFileSystem = new LocalFileSystem();
    $this->expectExceptionMessage('Directory does not exist');
    $localFileSystem->put('/failing/test.txt', 'test');
  }

  public function testCannotPutFileInNonWritableDirectory(): void {
    $localFileSystem = new LocalFileSystem();
    $this->expectExceptionMessage('Directory is not writable');
    $localFileSystem->put('/etc/test.txt', 'test');
  }

  public function testCanGetFile(): void {
    $localFileSystem = new LocalFileSystem();
    $localFileSystem->put('/tmp/test.txt', 'test');
    $this->assertEquals($localFileSystem->get('/tmp/test.txt'),'test');
  }

  public function testCannotGetNonExistingFile(): void {
    $localFileSystem = new LocalFileSystem();
    $this->expectExceptionMessage('File does not exist');
    $localFileSystem->get('/dbdksqjlfdkldqnldsqnldsq.txt');
  }

  public function testCannotGetNonReadableFile(): void {
    $localFileSystem = new LocalFileSystem();
    $this->expectExceptionMessage('File is not readable');
    $localFileSystem->get('/etc/shadow');
  }
}