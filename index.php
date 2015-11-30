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
  include('loginframe.php');
}
 