<?php

function s_autoload($class) {
  $class_dir = "include/class/";
  $file_path = '';
  $file_path = $class_dir . "{$class}.php";
  $real_path = ROOT . strtolower($file_path);
  if (!file_exists($real_path)) {
    throw new Exception('Ooops, system file is losing: ' . $real_path);
  } else {
    require_once $real_path;
  }
}

spl_autoload_register('s_autoload', true);

function APOST() {
  return json_decode(file_get_contents("php://input"), true);
}

function ssetcookie($name, $value = '', $exp = 2592000) {
  $exp = $value ? time() + $exp : '1';
  setcookie($name, $value, $exp, '/');
}
