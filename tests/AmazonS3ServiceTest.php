<?php

require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Aws\S3\S3MultiRegionClient;
use Aws\Exception\AwsException;

class AmazonS3ServiceTest extends TestCase {

  private $s3;

  public function setUp(): void {
    // create a localstack s3 client
    $this->s3 = new S3MultiRegionClient([
      'version'                 => 'latest',
      'credentials'             => [
        'key'    => 'accessKeyId',
        'secret' => 'secretAccessKey',
      ],
      'endpoint'                => 'http://localhost:4566',
      // required for localstack to work
      'use_path_style_endpoint' => true,
    ]);

    // create a test bucket in localstack
    $this->s3->createBucket([
      'Bucket' => 'test',
      'CreateBucketConfiguration' => [
        'LocationConstraint' => 'us-east-1',
      ],
    ]);
    $this->s3->putPublicAccessBlock([
      'Bucket' => 'test',
      'PublicAccessBlockConfiguration' => [
        'BlockPublicAcls' => false,
      ],
    ]);

    // create a non public bucket in localstack
    $this->s3->createBucket([
      'Bucket' => 'non-public',
      'CreateBucketConfiguration' => [
        'LocationConstraint' => 'us-east-1',
      ],
    ]);
    $this->s3->putPublicAccessBlock([
      'Bucket' => 'non-public',
      'PublicAccessBlockConfiguration' => [
        'BlockPublicAcls' => true,
      ],
    ]);
  }

  public function tearDown(): void {
    // delete created buckets in localstack
    $this->s3->deleteBucket([
      'Bucket' => 'test',
    ]);
    $this->s3->deleteBucket([
      'Bucket' => 'non-public',
    ]);
  }

  public function testPutObject(): void {
    $amazonS3Service = new AmazonS3Service('accessKeyId', 'secretAccessKey', 'http://localhost:4566');
    $amazonS3Service->put("test/test.txt", "It works!");
    $this->assertEquals($result = $this->s3->GetObject([
      'Bucket' => $bucketName,
      'Key'    => $objectName,
    ]),'It works!');
  }

  public function testPutObjectWithNonExistantBucket(): void {
    $amazonS3Service = new AmazonS3Service('accessKeyId', 'secretAccessKey', 'http://localhost:4566');
    $this->expectExceptionMessage('Bucket does not exist');
    $amazonS3Service->put("non-existent/test.txt", "It works!");
  }

  public function testPutObjectWithNonPublicBucket(): void {
    $amazonS3Service = new AmazonS3Service('accessKeyId', 'secretAccessKey', 'http://localhost:4566');
    $this->expectExceptionMessage('Bucket does not allow public ACL on objects');
    $amazonS3Service->put("non-public/test.txt", "It works!");
  }

  public function testGetObject(): void {
    $amazonS3Service = new AmazonS3Service('accessKeyId', 'secretAccessKey', 'http://localhost:4566');
    $amazonS3Service->put("test/test.txt", "It works!");
    $this->assertEquals($amazonS3Service->get("test/test.txt"),'It works!');
  }

  public function testGetObjectWithNonExistantBucket(): void {
    $amazonS3Service = new AmazonS3Service('accessKeyId', 'secretAccessKey', 'http://localhost:4566');
    $this->expectExceptionMessage('Bucket does not exist');
    $amazonS3Service->get("non-existent/test.txt");
  }

  public function testGetObjectWithNonExistantObject(): void {
    $amazonS3Service = new AmazonS3Service('accessKeyId', 'secretAccessKey', 'http://localhost:4566');
    $this->expectExceptionMessage('Object does not exist');
    $amazonS3Service->get("test/non-existent.txt");
  }
}