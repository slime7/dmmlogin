<?php

class kanlogin
{
  /**
   * login data
   *
   * @var array
   */
  public $loginData;
  /**
   * login email
   *
   * @var string
   */
  private $email;
  /**
   * login password
   *
   * @var string
   */
  private $password;
  /**
   * game link return type
   *
   * @var string
   */
  private $type;
  /**
   * is remember
   *
   * @var boolean
   */
  private $remember;
  /**
   * urls used
   */
  private $urls = [
    'login' => 'https://accounts.dmm.com/service/login/password/=/ref=main_top',
    'gettoken' => 'https://accounts.dmm.com/service/api/get-token',
    'auth' => 'https://accounts.dmm.com/service/login/password/authenticate',
    'game' => 'http://www.dmm.com/netgame/social/-/gadgets/=/app_id=854854/',
    'make_request' => 'http://osapi.dmm.com/gadgets/makeRequest',
    'get_world' => 'http://203.104.209.7/kcsapi/api_world/get_id/%s/1/%d',
    'get_flash' => 'http://%s/kcsapi/api_auth_member/dmmlogin/%s/1/%d',
    'flash' => 'http://%s/kcs/mainD2.swf?api_token=%s&api_starttime=%d',
    'html5' => 'http://%s/kcs2/index.php?api_root=/kcsapi&voice_root=/kcs/sound&osapi_root=osapi.dmm.com&version=4.0.0.7&api_token=%s&api_starttime=%d',
  ];
  /**
   * kan_colle world server ip
   */
  private $world_ip_list = [
    '203.104.209.71',
    '203.104.209.87',
    '125.6.184.16',
    '125.6.187.205',
    '125.6.187.229',
    '203.104.209.134',
    '203.104.209.167',
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

  /**
   * kanlogin constructor.
   * @param string $email
   * @param string $password
   * @param string $type
   * @param boolean $remember
   *
   * set login form
   */
  public function __construct($email, $password, $type, $remember = false) {
    $this->email = $email;
    $this->password = $password;
    $this->type = $type;
    $this->remember = $remember;
  }

  /**
   * get DMM_TOKEN
   *
   * @return bool
   */
  private function get_dmm_token() {
    global $json;
    $getLoginPage = new scurl();
    if (proxy) {
      $getLoginPage->setProxy(proxy, proxy_port);
    }
    $loginPage = $getLoginPage->get($this->urls['login']);
    preg_match('#csrf-token.*?"(?<csrf_token>[a-z0-9]{32})".*?"csrf-http-dmm-token.*?"(?<csrf_http_dmm_token>[a-z0-9]{32})#is', $loginPage['data'], $tokens);
    unset($loginPage['data']);
    $csrf_token = isset($tokens['csrf_token']) ? $tokens['csrf_token'] : '';
    $csrf_http_dmm_token = isset($tokens['csrf_http_dmm_token']) ? $tokens['csrf_http_dmm_token'] : '';

    if (!!$csrf_token && !!$csrf_http_dmm_token) {
      $this->loginData['csrf_token'] = $csrf_token;
      $this->loginData['csrf_http_dmm_token'] = $csrf_http_dmm_token;

      return true;
    } else {
      $json->setMsg('get CSRF-TOKEN failure');

      return false;
    }
  }

  /**
   * get login token
   *
   * @return bool
   */
  private function get_login_token() {
    global $json;
    $getLoginTokens = new scurl();
    if (proxy) {
      $getLoginTokens->setProxy(proxy, proxy_port);
    }
    $ajaxHeaders = ['http-dmm-token: ' . $this->loginData['csrf_http_dmm_token'], 'X-Requested-With: XMLHttpRequest'];
    $tokensPage = $getLoginTokens->post(
      $this->urls['gettoken'], ['token' => $this->loginData['csrf_token']], ['headers' => $ajaxHeaders]
    );

    if ($tokensPage['status'] == 200) {
      $this->loginData['ajax_tokens'] = json_decode($tokensPage['data'], true)['body'];

      return true;
    } else {
      $json->setMsg('get token failure');

      return false;
    }
  }

  /**
   * login dmm
   *
   * @return bool
   */
  private function login_dmm() {
    global $json;
    $doLogin = new scurl();
    if (proxy) {
      $doLogin->setProxy(proxy, proxy_port);
    }
    $loginParams = [
      'token' => $this->loginData['ajax_tokens']['token'],
      'login_id' => $this->email,
      'save_login_id' => 0,
      'password' => $this->password,
      'save_password' => 0,
      'idKey' => $this->email,
      'pwKey' => $this->password,
      'path' => '',
      'prompt' => ''
    ];
    $loginResult = $doLogin->post(
      $this->urls['auth'], $loginParams
    );

    if ($loginResult['status'] == 302) {
      $this->loginData['dmm_cookie'] = '';
      foreach ($loginResult['headers'] as $h) {
        $line = explode(':', $h, 2);
        if (strtolower($line[0]) == 'set-cookie') {
          $c = trim(explode(';', $line[1])[0]);
          if (explode('=', $c)[1] !== ''
            && strpos($c, 'login_id2') === false && strpos($c, 'password') === false
            && strpos($c, 'secid') === false && strpos($c, 'login_secure_id') === false) {
            $this->loginData['dmm_cookie'] .= !!$this->loginData['dmm_cookie'] ? '; ' . $c : $c;
          }
        }
      }

      return true;
    } else {
      $json->setMsg('login failure');

      return false;
    }
  }

  /**
   * get game osapi link
   *
   * @return bool
   */
  private function get_osapi_link() {
    global $json;
    $getGamePage = new scurl();
    if (proxy) {
      $getGamePage->setProxy(proxy, proxy_port);
    }
    $gamePage = $getGamePage->get(
      $this->urls['game'], [], ['cookie' => $this->loginData['dmm_cookie']]
    );

    if ($gamePage['status'] == 200) {
      $html = new simple_html_dom();
      $html->load($gamePage['data']);
      $link = $html->find('iframe#game_frame', 0)->src;
      if (!$link) {
        $json->setMsg('get game page failure');

        return false;
      }
      $html->clear();

      $this->loginData['osapi'] = htmlspecialchars_decode($link);
      $json->add('link', $this->loginData['osapi']);
      $json->add('link_encode', urlencode($this->loginData['osapi']));

      /*
      if($this->remember) {
        ssetcookie('login_string', base64_encode($this->loginData['dmm_cookie']));
      } else {
        ssetcookie('login_string', base64_encode($this->loginData['dmm_cookie']), 500);
      }
      */

      return true;
    } else {
      $json->setMsg('get game page failure');

      return false;
    }
  }

  /**
   * get game server
   *
   * @return bool
   */
  private function get_game_world() {
    global $json;
    $getWorld = new scurl();
    if (proxy) {
      $getWorld->setProxy(proxy, proxy_port);
    }
    parse_str(parse_url($this->loginData['osapi'], PHP_URL_QUERY), $this->loginData['osapi_query']);
    $getWorldUrl = sprintf($this->urls['get_world'], $this->loginData['osapi_query']['owner'], time() * 1000);
    $getWorldHeader = ['Referer: ' . $this->loginData['osapi']];
    $world = $getWorld->get(
      $getWorldUrl, [], ['headers' => $getWorldHeader]
    );

    if ($world['status'] == 200) {
      $world_svdata = json_decode(substr($world['data'], 7), true);
      if ($world_svdata['api_result'] == 1) {
        $this->loginData['world_id'] = $world_svdata['api_data']['api_world_id'];
        $this->loginData['world_ip'] = $this->world_ip_list[$this->loginData['world_id'] - 1];

        return true;
      } else {
        $json->setMsg('world error.');

        return false;
      }
    } else {
      $json->setMsg('get world failure.');

      return false;
    }
  }

  /**
   * get game swf
   *
   * @return bool
   */
  private function get_game_swf_link() {
    global $json;
    $getApiToken = new scurl();
    if (proxy) {
      $getApiToken->setProxy(proxy, proxy_port);
    }
    $getFlashUrl = sprintf($this->urls['get_flash'], $this->loginData['world_ip'], $this->loginData['osapi_query']['owner'], time() * 1000);
    $getFlashData = [
      'url' => $getFlashUrl,
      'httpMethod' => 'GET',
      'authz' => 'signed',
      'st' => $this->loginData['osapi_query']['st'],
      'contentType' => 'JSON',
      'numEntries' => '3',
      'getSummaries' => 'false',
      'signOwner' => 'true',
      'signViewer' => 'true',
      'gadget' => 'http://203.104.209.7/gadget.xml',
      'container' => 'dmm'
    ];
    $apiToken = $getApiToken->post(
      $this->urls['make_request'], $getFlashData
    );

    if ($apiToken['status'] == 200) {
      $apiToken_data = json_decode(substr($apiToken['data'], 27), true);
      if ($apiToken_data[$getFlashUrl]['rc'] == 200) {
        $apiToken_data[$getFlashUrl]['body'] = json_decode(substr($apiToken_data[$getFlashUrl]['body'], 7), true);
        if ($apiToken_data[$getFlashUrl]['body']['api_result'] == 1) {
          $this->loginData['api_token'] = $apiToken_data[$getFlashUrl]['body']['api_token'];
          $this->loginData['api_starttime'] = $apiToken_data[$getFlashUrl]['body']['api_starttime'];
          $this->loginData['flash_base'] = 'http://' . $this->loginData['world_ip'] . '/kcs/';
          $this->loginData['flash'] = sprintf($this->urls['html5'], $this->loginData['world_ip'], $this->loginData['api_token'], $this->loginData['api_starttime']);

          $json->add('flash_base', $this->loginData['flash_base']);
          $json->add('flash', $this->loginData['flash']);
          $json->add('flash_encode', urlencode($this->loginData['flash']));

          return true;
        } else {
          $json->setMsg('api token error.');

          return false;
        }
      } else {
        $json->setMsg('api token failure.');

        return false;
      }
    } else {
      $json->setMsg('get game swf link failure.');

      return false;
    }
  }

  /**
   * do login
   */
  public function login() {
    $is_get_flash = ($this->type != 'redirect2');
    $get_osapi = true;
    if (!$this->get_dmm_token()) {
      return false;
    }
    if (!$this->get_login_token()) {
      return false;
    }
    if (!$this->login_dmm()) {
      return false;
    }
    if (!$this->get_osapi_link()) {
      return false;
    }

    if ($is_get_flash) {
      $get_flash = true;
      if (!$this->get_game_world()) {
        return false;
      }
      if (!$this->get_game_swf_link()) {
        return false;
      }

      return $get_osapi && $get_flash;
    } else {
      return $get_osapi;
    }
  }

  /**
   * get login data
   *
   * @return array
   */
  public function loginData() {
    return $this->loginData;
  }
}