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
$urls = [
  'login' => 'https://www.dmm.com/my/-/login/',
  'gettoken' => 'https://www.dmm.com/my/-/login/ajax-get-token/',
  'auth' => 'https://www.dmm.com/my/-/login/auth/',
  'game' => 'http://www.dmm.com/netgame/social/-/gadgets/=/app_id=854854/',
  'make_request' => 'http://osapi.dmm.com/gadgets/makeRequest',
  'get_world' => 'http://203.104.209.7/kcsapi/api_world/get_id/%s/1/%d',
  'get_flash' => 'http://%s/kcsapi/api_auth_member/dmmlogin/%s/1/%d',
  'flash' => '//%s/kcs/mainD2.swf?api_token=%s&api_starttime=%d'
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

      /* get DMM_TOKEN */
      $getLoginPage = new scurl();
      if (proxy) {
        $getLoginPage->setProxy(proxy_addr, proxy_port);
      }
      $loginPage = $getLoginPage->get($urls['login']);
      preg_match('#DMM_TOKEN.*?"(?<DMM_TOKEN>[a-z0-9]{32})".*?"token.*?"(?<token>[a-z0-9]{32})#is', $loginPage['data'], $tokens);
      unset($loginPage['data']);
      $DMM_TOKEN = isset($tokens['DMM_TOKEN']) ? $tokens['DMM_TOKEN'] : '';
      $post_token = isset($tokens['token']) ? $tokens['token'] : '';

      if ($loginPage['status'] == 200 && !!$DMM_TOKEN && !!$post_token) {
        $first = ['DMM_TOKEN' => $DMM_TOKEN, 'token' => $post_token];
      } else {
        $json->add('response', $loginPage);
        break;
      }

      /* get login token */
      $getLoginTokens = new scurl();
      if (proxy) {
        $getLoginTokens->setProxy(proxy_addr, proxy_port);
      }
      $ajaxHeaders = ['DMM_TOKEN: ' . $DMM_TOKEN, 'X-Requested-With: XMLHttpRequest'];
      $tokensPage = $getLoginTokens->post(
        $urls['gettoken'], ['token' => $post_token], ['headers' => $ajaxHeaders]
      );

      if ($tokensPage['status'] == 200) {
        $ajax_tokens = json_decode($tokensPage['data'], true);
      } else {
        $json->setMsg('get token failure');
        break;
      }

      /* try login */
      $doLogin = new scurl();
      if (proxy) {
        $doLogin->setProxy(proxy_addr, proxy_port);
      }
      $loginParams = [
        'token' => $ajax_tokens['token'],
        'login_id' => $email,
        'save_login_id' => 0,
        'password' => $password,
        'use_auto_login' => 0,
        $ajax_tokens['login_id'] => $email,
        $ajax_tokens['password'] => $password,
      ];
      $loginResult = $doLogin->post(
        $urls['auth'], $loginParams
      );

      if ($loginResult['status'] == 302) {
        $cookie = '';
        foreach ($loginResult['headers'] as $h) {
          $line = explode(":", $h, 2);
          if ($line[0] == 'Set-Cookie') {
            $c = explode(";", $line[1])[0];
            $cookie .= !!$cookie ? '; ' . $c : $c;
          }
        }
      } else {
        $json->setMsg('login failure');
        break;
      }

      /* get game page data */
      $getGamePage = new scurl();
      if (proxy) {
        $getGamePage->setProxy(proxy_addr, proxy_port);
      }
      $gamePage = $getGamePage->get(
        $urls['game'], [], ['cookie' => $cookie]
      );

      if ($link = gameLink($cookie)) {

        $json->add('link', $link);
        $json->add('link_encode', urlencode($link));

        if (!!$post['remember']) {
          ssetcookie('login_string', base64_encode($cookie));
        } else {
          ssetcookie('login_string', base64_encode($cookie), 500);
        }
      } else {
        $json->setMsg('get game page failure');
        break;
      }

      /* get game world */
      $getWorld = new scurl();
      if (proxy) {
        $getWorld->setProxy(proxy_addr, proxy_port);
      }
      parse_str(parse_url($link, PHP_URL_QUERY), $osapi_query);
      $getWorldUrl = sprintf($urls['get_world'], $osapi_query['owner'], time() * 1000);
      $getWorldHeader = ['Referer: ' . $link];
      $world = $getWorld->get(
        $getWorldUrl, [], ['headers' => $getWorldHeader]
      );
      $world_svdata = json_decode(substr($world['data'], 7), true);
      if ($world_svdata['api_result'] == 1) {
        $world_id = $world_svdata['api_data']['api_world_id'];
        $world_ip = $world_ip_list[$world_id - 1];
      } else {
        $json->setMsg('get world failure.');
        break;
      }

      /* get api token */
      $getApiToken = new scurl();
      if (proxy) {
        $getApiToken->setProxy(proxy_addr, proxy_port);
      }
      $getFlashUrl = sprintf($urls['get_flash'], $world_ip, $osapi_query['owner'], time() * 1000);
      $getFlashData = [
        'url' => $getFlashUrl,
        'httpMethod' => 'GET',
        'authz' => 'signed',
        'st' => $osapi_query['st'],
        'contentType' => 'JSON',
        'numEntries' => '3',
        'getSummaries' => 'false',
        'signOwner' => 'true',
        'signViewer' => 'true',
        'gadget' => 'http://203.104.209.7/gadget.xml',
        'container' => 'dmm'
      ];
      $apiToken = $getApiToken->post(
        $urls['make_request'], $getFlashData
      );
      $apiToken_data = json_decode(substr($apiToken['data'], 27), true);
      if ($apiToken_data[$getFlashUrl]['rc'] == 200) {
        $apiToken_data[$getFlashUrl]['body'] = json_decode(substr($apiToken_data[$getFlashUrl]['body'], 7), true);
        if ($apiToken_data[$getFlashUrl]['body']['api_result'] == 1) {
          $api_token = $apiToken_data[$getFlashUrl]['body']['api_token'];
          $api_starttime = $apiToken_data[$getFlashUrl]['body']['api_starttime'];
        } else {
          $json->setMsg('get api token failure.');
          break;
        }
      } else {
        $json->setMsg('get api token failure.');
        break;
      }
      $falsh = sprintf($urls['flash'], $world_ip, $api_token, $api_starttime);
      $json->add('flash_base', '//' . $world_ip . '/kcs/');
      $json->add('flash', $falsh);
      $json->add('flash_encode', urlencode($falsh));

      $json->success();
      break;

    default :
      $json->setMsg('watch your magic');
      break;
  }
} else {
  $json->setMsg('watch your magic');
}

exit($json);

function gameLink($cookie) {
  global $urls;
  $getGamePage = new scurl();
  if (proxy) {
    $getGamePage->setProxy(proxy_addr, proxy_port);
  }
  $gamePage = $getGamePage->get(
    $urls['game'], [], ['cookie' => $cookie]
  );

  if ($gamePage['status'] == 200) {
    $html = new simple_html_dom();
    $html->load($gamePage['data']);
    $link = $html->find('iframe#game_frame', 0)->src;
    $html->clear();

    return htmlspecialchars_decode($link);
  }

  return false;
}
