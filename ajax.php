<?php

define('ROOT', dirname(__FILE__) . '/');
include_once ROOT . 'include/functions.php';

$json = new sjsonpack();
$post = APOST();

if (isset($post['action'])) {
  switch ($post['action']) {
    case 'login':
      if (!preg_match('#\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*#', $post['email'])) {
        $json->setMsg('invailed email');
        break;
      }

      $getLoginPage = new scurl();
      $loginPage = $getLoginPage->get('https://www.dmm.com/my/-/login/');
      preg_match('#DMM_TOKEN.*?"(?<DMM_TOKEN>[a-z0-9]{32})".*?"token.*?"(?<token>[a-z0-9]{32})#is', $loginPage['data'], $tokens);
      unset($loginPage['data']);
      $DMM_TOKEN = isset($tokens['DMM_TOKEN']) ? $tokens['DMM_TOKEN'] : '';
      $post_token = isset($tokens['token']) ? $tokens['token'] : '';

      if ($loginPage['status'] == 200 && !!$DMM_TOKEN && !!$post_token) {
        $first = ['DMM_TOKEN' => $DMM_TOKEN, 'token' => $post_token];
      } else {
        $json->setMsg('get DMM_TOKEN failure');
        break;
      }

      $getLoginTokens = new scurl();
      $ajaxHeaders = ['DMM_TOKEN: ' . $DMM_TOKEN, 'X-Requested-With: XMLHttpRequest'];
      $tokensPage = $getLoginTokens->post(
              'https://www.dmm.com/my/-/login/ajax-get-token/', ['token' => $post_token], ['headers' => $ajaxHeaders]
      );

      if ($tokensPage['status'] == 200) {
        $ajax_tokens = json_decode($tokensPage['data'], true);
      } else {
        $json->setMsg('get token failure');
        break;
      }

      $doLogin = new scurl();
      $loginParams = [
          'token' => $ajax_tokens['token'],
          'login_id' => $post['email'],
          'save_login_id' => 0,
          'password' => $post['password'],
          'use_auto_login' => 0,
          $ajax_tokens['login_id'] => $post['email'],
          $ajax_tokens['password'] => $post['password'],
      ];
      $loginResult = $doLogin->post(
              'https://www.dmm.com/my/-/login/auth/', $loginParams
      );

      if ($loginResult['status'] == 302) {
        $cookie = '';
        foreach ($loginResult['headers'] as $h) {
          $line = explode(":", $h, 2);
          if ($line[0] == 'Set-Cookie') {
            $c = explode(";", $line[1])[0];
            $cookie .=!!$cookie ? '; ' . $c : $c;
          }
        }
      } else {
        $json->setMsg('login failure');
        break;
      }

      $getGamePage = new scurl();
      $gamePage = $getGamePage->get(
              'http://www.dmm.com/netgame/social/-/gadgets/=/app_id=854854/', [], ['cookie' => $cookie]
      );

      if ($link = gameLink($cookie)) {

        $json->add('link', $link);
        $json->add('link_encode', urlencode($link));


        ssetcookie('iframe_url', $link);
        if (!!$post['remember']) {
          ssetcookie('login_string', base64_encode($cookie));
        } else {
          ssetcookie('login_string', base64_encode($cookie), 500);
        }
      } else {
        $json->setMsg('login failure');
        break;
      }

      $json->success();
      break;

    case 'usecookie':
      $login_string = isset($_COOKIE['login_string']) ? $_COOKIE['login_string'] : '';
      $cookie = base64_decode($login_string);
      if (!$cookie) {
        $json->srtMsg('invailed cookie');
        break;
      }

      if ($link = gameLink($cookie)) {
        $json->add('link', $link);
        $json->add('link_encode', urlencode($link));
        $json->success();
        ssetcookie('iframe_url', $link);
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

function gameLink($cookie) {
  $getGamePage = new scurl();
  $gamePage = $getGamePage->get(
          'http://www.dmm.com/netgame/social/-/gadgets/=/app_id=854854/', [], ['cookie' => $cookie]
  );

  if ($gamePage['status'] == 200) {
    $html = new simple_html_dom();
    $html->load($gamePage['data']);
    $link = $html->find('iframe#game_frame', 0)->src;
    $html->clear();

    return $link;
  }

  return false;
}
