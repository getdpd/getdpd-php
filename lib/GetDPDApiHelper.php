<?php

/*
 * This file is part of the DPD API package.
 * (c) 2010-2016 Portal Labs, LLC <contact@portallabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class GetDPDApiError extends Exception { }
class GetDPDResourceNotFoundError extends GetDPDApiError { }
class GetDPDAuthenticationError extends GetDPDApiError { }

require_once 'GetDPDApiCollection.php';

class GetDPDApiHelper
{
  private
    $base_uri = "https://api.getdpd.com/v2/",
    $headers = null,
    $user = null,
    $key = null;

  public function __construct($user, $key, $development_uri = null)
  {
    $this->user = $user;
    $this->key = $key;
    if($development_uri)
      $this->base_uri = $development_uri;
  }

  // Gets a singular resource, returns an array of data
  public function get($action, $parameters = array())
  {
    return $this->call('GET', $action, $parameters);
  }

  // Gets a collection resource, returns an iterator to cycle across data
  // GetDPDApiCollection seemlessly handles paging over an entire resource if
  // you omit the page parameter
  public function getCollection($action, $parameters = array())
  {
    return new GetDPDApiCollection($this, $action, $parameters);
  }

  public function call($method, $action, $parameters = array())
  {
    $this->headers = array();
    $url = $this->buildURI($action);

    if($method == 'GET' && count($parameters) > 0)
      $url.= "?".http_build_query($parameters);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, "");
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->key}");
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, "parseHeader"));

    switch($method)
    {
      case 'POST':
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
      break;
      case 'DELETE':
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      break;
      case 'PUT':
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        //curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Content-Type: application/x-www-form-urlencoded\n"));
      break;
    }

    $raw = curl_exec($ch);
    if($raw === false)
      throw new GetDPDApiError(curl_error($ch), null);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if($http_code == '404')
      throw new GetDPDResourceNotFoundError($url);
    else if($http_code == '401')
      throw new GetDPDAuthenticationError("Unable to verify your getdpd.com API credentials.");
    else if($http_code != '200')
      throw new GetDPDApiError("Received error response: {$http_code}");

    if(strpos($content_type, 'application/json') !== false)
      return json_decode($raw, true);
    else
      throw new GetDPDApiError('Response recieved was not valid JSON.');
  }

  public function getHeaders()
  {
    return $this->headers;
  }

  protected function buildURI($action)
  {
    return $this->base_uri.ltrim($action, '/');
  }

  protected function parseHeader($curl, $header)
  {
    list($name, $value) = explode(':', $header, 2) + array(null, null);
    if(trim($value))
      $this->headers[trim($name)] = trim($value);

    return strlen($header);
  }

}
