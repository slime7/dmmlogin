<?php

//namespace Network\Curl;

/**
 * Curl
 *
 * Simple cURL wrapper for basic HTTP requests. Support available
 * for GET, POST, PUT, PATCH and DELETE
 *
 * @author    dsyph3r <d.syph.3r@gmail.com>
 */
class scurl
{
  /**
   * Constants for available HTTP methods
   */
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const PATCH = 'PATCH';
  const DELETE = 'DELETE';

  /**
   * @var cURL handle
   */
  private $curl;

  private $proxy = false;
  private $proxy_addr;
  private $proxy_port;

  /**
   * Create the cURL resource
   */
  public function __construct() {
    $this->curl = curl_init();
  }

  /**
   * Clean up the cURL handle
   */
  public function __destruct() {
    if (is_resource($this->curl)) {
      curl_close($this->curl);
    }
  }

  /**
   * Get the cURL handle
   *
   * @return  cURL            cURL handle
   */
  public function getCurl() {
    return $this->curl;
  }

  /**
   * Make a HTTP GET request
   *
   * @param   string $url Full URL including protocol
   * @param   array $params Any GET params
   * @param   array $options Additional options for the request
   * @return  array                 Response
   */
  public function get($url, $params = array(), $options = array()) {
    return $this->request($url, self::GET, $params, $options);
  }

  /**
   * Make a HTTP POST request
   *
   * @param   string $url Full URL including protocol
   * @param   array $params Any POST params
   * @param   array $options Additional options for the request
   * @return  array                 Response
   */
  public function post($url, $params = array(), $options = array()) {
    return $this->request($url, self::POST, $params, $options);
  }

  /**
   * Make a HTTP PUT request
   *
   * @param   string $url Full URL including protocol
   * @param   array $params Any PUT params
   * @param   array $options Additional options for the request
   * @return  array                 Response
   */
  public function put($url, $params = array(), $options = array()) {
    return $this->request($url, self::PUT, $params, $options);
  }

  /**
   * Make a HTTP PATCH request
   *
   * @param   string $url Full URL including protocol
   * @param   array $params Any PATCH params
   * @param   array $options Additional options for the request
   * @return  array                 Response
   */
  public function patch($url, $params = array(), $options = array()) {
    return $this->request($url, self::PATCH, $params, $options);
  }

  /**
   * Make a HTTP DELETE request
   *
   * @param   string $url Full URL including protocol
   * @param   array $params Any DELETE params
   * @param   array $options Additional options for the request
   * @return  array                 Response
   */
  public function delete($url, $params = array(), $options = array()) {
    return $this->request($url, self::DELETE, $params, $options);
  }

  /**
   * Make a HTTP request
   *
   * @param   string $url Full URL including protocol
   * @param   string $method HTTP method
   * @param   array $params Any params
   * @param   array $options Additional options for the request
   * @return  array                 Response
   */
  protected function request($url, $method = self::GET, $params = array(), $options = array()) {
    curl_setopt($this->curl, CURLOPT_HEADER, true);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($this->curl, CURLOPT_URL, $url);

    if ($this->proxy) {
      curl_setopt($this->curl, CURLOPT_PROXY, $this->proxy_addr);
      curl_setopt($this->curl, CURLOPT_PROXYPORT, $this->proxy_port);
      curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }

    if (isset($options['payload']) && $options['payload'] == true) {
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
    } else {
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    //curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);

    //302
    if (isset($options['302'])) {
      curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, !!$options['302']);
    } else {
      curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
    }

    //check for custom cookie
    if (isset($options['cookie'])) {
      curl_setopt($this->curl, CURLOPT_COOKIE, $options['cookie']);
    }

    // Check for custom headers
    if (isset($options['headers']) && count($options['headers']))
      curl_setopt($this->curl, CURLOPT_HTTPHEADER, $options['headers']);

    // Check for basic auth
    if (isset($options['auth']['type']) && "basic" === $options['auth']['type'])
      curl_setopt($this->curl, CURLOPT_USERPWD, $options['auth']['username'] . ':' . $options['auth']['password']);

    $response = $this->doCurl();

    // Separate headers and body
    $headerSize = $response['curl_info']['header_size'];
    $header = substr($response['response'], 0, $headerSize);
    $body = substr($response['response'], $headerSize);
    //$responseSplit = preg_split('/((?:\\r?\\n){2})/', $response['response']);
    //$responseCount = count($responseSplit);

    $results = array(
      'curl_info' => $response['curl_info'],
      'status' => $response['curl_info']['http_code'],
      'error' => $response['error'],
      'headers' => $this->splitHeaders($header),
      //'headers_o'     => $header,
      'data' => $body,
    );

    return $results;
  }

  /**
   * Split the HTTP headers
   *
   * @param   string $rawHeaders Raw HTTP headers
   * @return  array                   Key/Value headers
   */
  protected function splitHeaders($rawHeaders) {
    $headers = array();

    $headerLines = explode("\r\n", trim($rawHeaders));
    $headers['HTTP'] = array_shift($headerLines);
    foreach ($headerLines as $line) {
      /*
                  $header = explode(":", $line, 2);
                  if( isset($header[1]) ) {
                    $headers[trim($header[0])] = trim($header[1]);
                  }
      */
      $headers[] = $line;
    }

    return $headers;
  }

  /**
   * Perform the Curl request
   *
   * @param   cURL Handle     $curl       The cURL handle to use
   * @return  array                       cURL response
   */
  protected function doCurl() {
    $response = curl_exec($this->curl);
    $curlInfo = curl_getinfo($this->curl);
    $error = curl_error($this->curl);

    $results = array(
      'curl_info' => $curlInfo,
      'response' => $response,
      'error' => $error
    );

    return $results;
  }

  public function setProxy($addr, $port) {
    if (!empty($addr) && !empty($port)) {
      $this->proxy = true;
      $this->proxy_addr = $addr;
      $this->proxy_port = $port;
    }
  }

  public function clearProxy() {
    $this->proxy = false;
    $this->proxy_addr = '';
    $this->proxy_port = '';
  }


}

/**
 * General Curl Exception
 */
class CurlException extends \Exception
{
}