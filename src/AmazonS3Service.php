<?php

require 'vendor/autoload.php';

use Aws\S3\S3MultiRegionClient;
use Aws\Exception\AwsException;

class AmazonS3Service implements GenericStorage {

  private $s3;

  public function __construct($accessKeyId, $secretAccessKey, $endpoint=null) {
    try {
      if (!isset($endpoint)) {
        $this->s3 = new S3MultiRegionClient([
          'version'     => 'latest',
          'credentials' => [
            'key'    => $accessKeyId,
            'secret' => $secretAccessKey,
          ],
        ]);
      } else {
        $this->s3 = new S3MultiRegionClient([
          'version'     => 'latest',
          'credentials' => [
            'key'    => $accessKeyId,
            'secret' => $secretAccessKey,
          ],
          'endpoint'    => $endpoint,
        ]);
      }
    } catch (AwsException $e){
      echo $e->getMessage().PHP_EOL;
    }
  }

  private function bucketExists($bucketName) {
    try {
      $buckets = $this->s3->listBuckets([])['Buckets'];
      foreach ($buckets as $bucket) {
        if ($bucket['Name'] == $bucketName) {
          return true;
        }
      }
      return false;
    } catch (AwsException $e) {
      print $e->getMessage().PHP_EOL;
    }
  }

  private function objectExists($bucketName, $objectName) {
    try {
      $objects = $this->s3->listObjects(['Bucket' => $bucketName])['Contents'];
      foreach ($objects as $object) {
        if($object['Key'] == $objectName) {
          return true;
        }
      }
      return false;
    } catch (AwsException $e) {
      print $e->getMessage().PHP_EOL;
    }
  }

  private function bucketIsPublic($bucketName) {
    try {
      $policy = $this->s3->getPublicAccessBlock([
        'Bucket' => $bucketName,
      ]);
      // as we only put objects with 'public-read' ACL, we need this setting set on bucket
      // we return the opposite of the value because bucket is public when value is false
      return !$policy['PublicAccessBlockConfiguration']['BlockPublicAcls'];
    } catch (AwsException $e) {
      print $e->getMessage().PHP_EOL;
    }
  }

  public function put($path, $data) {
    $bucketName = dirname($path);
    $objectName = basename($path);
    if (!$this->bucketExists($bucketName)) {
      throw new Exception('Bucket does not exist');
    } elseif (!$this->bucketIsPublic($bucketName)) {
      throw new Exception('Bucket does not allow public ACL on objects');
    } else {
      try {
        $this->s3->putObject([
          'Bucket' => $bucketName,
          'Key'    => $objectName,
          'Body'   => $data,
          'ACL'    => 'public-read',
        ]);
      } catch (AwsException $e) {
        print $e->getMessage().PHP_EOL;
      }
    }
  }

  public function get($path) {
    $bucketName = dirname($path);
    $objectName = basename($path);
    if (!$this->bucketExists($bucketName)) {
      throw new Exception('Bucket does not exist');
    } else if (!$this->objectExists($bucketName, $objectName)){
      throw new Exception('Object does not exist');
    } else {
      try {
        $result = $this->s3->GetObject([
          'Bucket' => $bucketName,
          'Key'    => $objectName,
        ]);
        return $result['Body']->getContents();
      } catch (AwsException $e) {
        print $e->getMessage().PHP_EOL;
      }
    }
  }
}