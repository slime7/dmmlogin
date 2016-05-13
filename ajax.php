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
$urls = [
  'login' => 'https://www.dmm.com/my/-/login/',
  'gettoken' => 'https://www.dmm.com/my/-/login/ajax-get-token/',
  'auth' => 'https://www.dmm.com/my/-/login/auth/',
  'game' => 'http://www.dmm.com/netgame/social/-/gadgets/=/app_id=854854/',
  'make_request' => 'http://osapi.dmm.com/gadgets/makeRequest',
  'get_world' => 'http://203.104.209.7/kcsapi/api_world/get_id/%s/1/%d',
  'get_flash' => 'http://%s/kcsapi/api_auth_member/dmmlogin/%s/1/%d',
  'flash' => 'http://%s/kcs/mainD2.swf?api_token=%s&api_starttime=%d'
];
$world_ip_list = [
  '203.104.209.71',
  '203.104.209.87',
  '125.6.184.16',
  '125.6.187.205',
  '125.6.187.229',
  '125.6.187.253',
  '125.6.188.25',
  '203.104.248.135',
  '125.6.189.7',
  '125.6.189.39',
  '125.6.189.71',
  '125.6.189.103',
  '125.6.189.135',
  '125.6.189.167',
  '125.6.189.215',
  '125.6.189.247',
  '203.104.209.23',
  '203.104.209.39',
  '203.104.209.55',
  '203.104.209.102'
];

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
