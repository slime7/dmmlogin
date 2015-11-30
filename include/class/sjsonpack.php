<?php

class sjsonpack {

  protected $success = 0;
  protected $msg = '';
  protected $data = [];
  protected $error;

  public function __construct() {
    ;
  }

  public function success() {
    $this->success = 1;
  }

  public function setMsg($msg) {
    $this->msg = $msg;
  }

  public function addError($err) {
    $this->error[] = $err;
  }

  public function add($key, $value) {
    $this->data[$key] = $value;
  }

  public function set($array) {
    $this->data = $array;
  }

  public function get($key) {
    return $this->data[$key];
  }

  public function forget($key) {
    unset($this->data[$key]);
  }

  public function all() {
    return $this->data;
  }

  public function toArray() {
    return [
        'success' => $this->success,
        'msg' => $this->msg,
        'data' => $this->data,
        'error' => $this->error,
    ];
  }

  public function toJson() {
    return json_encode($this->toArray());
  }

  public function __toString() {
    return $this->toJson();
  }

}
