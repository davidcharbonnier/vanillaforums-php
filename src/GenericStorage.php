<?php

interface GenericStorage {
  public function put($path, $data);

  public function get($path);
}