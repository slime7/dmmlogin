<?php

define('ROOT', dirname(__FILE__) . '/');
include_once ROOT . 'include/functions.php';

$iframe_url = isset($_COOKIE['iframe_url']) ? $_COOKIE['iframe_url'] : '';
$login_string = isset($_COOKIE['login_string']) ? $_COOKIE['login_string'] : '';

if (isset($_GET['relog'])) {
  ssetcookie('iframe_url', 'deleted', -1);
  $iframe_url = '';
}

if (!!$iframe_url) {
  include('gamepage.php');
} else {
  $email = $password = $si_string = '';
  if (isset($_COOKIE['si_string'])) {
    $si_string = explode("\t", authcode($_COOKIE['si_string']));
  }
  if (!!$si_string) {
    list($email, $password) = $si_string;
  }
  include('loginframe.php');
}
 