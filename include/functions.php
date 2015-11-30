<?php

//server info
$ConstServerInfo = [
    'Gadget' => "http://203.104.209.7/",
    'World_1' => "http://203.104.209.71/",
    'World_2' => "http://125.6.184.15/",
    'World_3' => "http://125.6.184.16/",
    'World_4' => "http://125.6.187.205/",
    'World_5' => "http://125.6.187.229/",
    'World_6' => "http://125.6.187.253/",
    'World_7' => "http://125.6.188.25/",
    'World_8' => "http://203.104.248.135/",
    'World_9' => "http://125.6.189.7/",
    'World_10' => "http://125.6.189.39/",
    'World_11' => "http://125.6.189.71/",
    'World_12' => "http://125.6.189.103/",
    'World_13' => "http://125.6.189.135/",
    'World_14' => "http://125.6.189.167/",
    'World_15' => "http://125.6.189.215/",
    'World_16' => "http://125.6.189.247/",
    'World_17' => "http://203.104.209.23/",
    'World_18' => "http://203.104.209.39/",
    'World_19' => "http://203.104.209.55/",
    'World_20' => "http://203.104.209.102/",
];

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

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
  $ckey_length = 4;
  $key = md5($key ? $key : 'key');
  $keya = md5(substr($key, 0, 16));
  $keyb = md5(substr($key, 16, 16));
  $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
  $cryptkey = $keya . md5($keya . $keyc);
  $key_length = strlen($cryptkey);
  $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
  $string_length = strlen($string);
  $result = '';
  $box = range(0, 255);
  $rndkey = array();
  for ($i = 0; $i <= 255; $i++) {
    $rndkey[$i] = ord($cryptkey[$i % $key_length]);
  }
  for ($j = $i = 0; $i < 256; $i++) {
    $j = ($j + $box[$i] + $rndkey[$i]) % 256;
    $tmp = $box[$i];
    $box[$i] = $box[$j];
    $box[$j] = $tmp;
  }
  for ($a = $j = $i = 0; $i < $string_length; $i++) {
    $a = ($a + 1) % 256;
    $j = ($j + $box[$a]) % 256;
    $tmp = $box[$a];
    $box[$a] = $box[$j];
    $box[$j] = $tmp;
    $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
  }
  if ($operation == 'DECODE') {
    if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
      return substr($result, 26);
    } else {
      return '';
    }
  } else {
    return $keyc . str_replace('=', '', base64_encode($result));
  }
}
