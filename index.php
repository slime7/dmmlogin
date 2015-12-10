<?php

define('ROOT', dirname(__FILE__) . '/');
include_once ROOT . 'include/functions.php';

$login_string = isset($_COOKIE['login_string']) ? $_COOKIE['login_string'] : '';

$email = $password = $si_string = '';
if (isset($_COOKIE['si_string'])) {
  $si_string = explode("\t", authcode($_COOKIE['si_string']));
}
if (!!$si_string) {
  list($email, $password) = $si_string;
}
include('appindex.html');
