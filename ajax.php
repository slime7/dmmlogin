<?php

define('ROOT', dirname(__FILE__) . '/');
include_once ROOT . 'include/functions.php';
date_default_timezone_set('Asia/Shanghai');
if (file_exists('proxy.php')) {
  include 'proxy.php';
}

if (!defined('proxy')) {
  define('proxy', false);
}
$json = new sjsonpack();
$post = APOST();
$email = $post['email'];
$password = !empty($post['password']) ? $post['password'] : '';
$remember = isset($post['remember']) ? $post['remember'] : false;
$lode_type = isset($post['loadType']) ? $post['loadType'] : 'include';

if (isset($post['action'])) {
  switch ($post['action']) {
    case 'usecookie':
      $si_string = '';
      if (isset($_COOKIE['si_string'])) {
        $si_string = explode("\t", authcode($_COOKIE['si_string']));
      }
      if (!!$si_string) {
        list($email, $password) = $si_string;
      }

    case 'login':
      if (!preg_match('#\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*#', $email)) {
        $json->setMsg('invailed email');
        break;
      }

      if ($remember) {
        ssetcookie('si_string', authcode(implode("\t", [$email, $password]), 'ENCODE'));
      }

      $gamelogin = new kanlogin($email, $password, $lode_type, $remember);
      $login_result = $gamelogin->login();

      if ($login_result) {
        $json->success();
      }
      break;

    default :
      $json->setMsg('watch your magic');
      break;
  }
} else {
  $json->setMsg('watch your magic');
}

exit($json);
