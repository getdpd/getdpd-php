<?php

class GetDPDApiError extends Exception { }
class GetDPDResourceNotFoundError extends GetDPDApiError { }
class GetDPDAuthenticationError extends GetDPDApiError { }

/*
 * This file is part of the DPD API package.
 * (c) 2010-2012 Portal Labs, LLC <contact@portallabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GetDPDApi
{
  const VERSION = '1.0';
  
  public $user = null;
  public $key = null;
  public $base_url = "api.getdpd.com/v2";
  public $protocol = "https";
  public $success = true;
  
  public function __construct($user, $key)
  {
    $this->user = $user;
    $this->key = $key;
  }
  
  public function ping()
  {
    return $this->doApiRequest('/', array(), 'GET');
  }
  
  public function listStorefronts()
  {
    return $this->doApiRequest('/storefronts', array(), 'GET');
  }
  
  public function getStorefront($id)
  {
    return $this->doApiRequest("/storefronts/{$id}", array(), "GET");
  }
  
  public function listProducts($storefront_id=null)
  {
    return $this->doApiRequest('/products', array('storefront_id' => $storefront_id), 'GET');
  }
  
  public function getProduct($id)
  {
    return $this->doApiRequest("/products/{$id}", array("id" => $id));
  }
  
  public function listPurchases($params=array())
  {
    return $this->doApiRequest("/purchases", $params, 'GET');
  }

  public function getPurchase($website_id, $id)
  {
    return $this->doApiRequest("/purchases/{$id}", array("id" => $id));
  }

  public function listSubscribers($storefront_id, $params=array())
  {
    return $this->doApiRequest("/storefronts/{$storefront_id}/subscribers", $params, 'GET');
  }
  
  public function getSubscriber($storefront_id, $id)
  {
    return $this->doApiRequest("/storefronts/{$storefront_id}/subscribers/{$id}", $params, 'GET');
  }
  
  public function verifySubscriber($storefront_id, $params)
  {
    return $this->doApiRequest("/storefronts/{$storefront_id}/subscribers/verify", $params, 'GET');
  }

  public function verifyNotification($params)
  {
    return $this->doApiRequest("/notification/verify", $params, 'POST');
  }

  public function doApiRequest($action, $params, $method='GET')
  {
    $this->success = false;
    
    $url = "{$this->protocol}://{$this->base_url}/{$action}";
    
    if($method == 'GET' && count($params) > 0)
      $url.= "?".http_build_query($params);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, "");
    curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->key}");
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    switch(strtoupper($method)) 
    {
      case 'POST':
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($params));
      break;
      case 'DELETE':
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      break;
      case 'PUT':
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($params));
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
      throw new GetDPDResourceNotFoundError($action);
    else if($http_code == '401')
      throw new GetDPDAuthenticationError("Unable to verify your getdpd.com API credentials.");
    else if($http_code != '200')
      throw new GetDPDApiError("Received error response: {$http_code}");
    
    if(strpos($content_type, 'application/json') !== false)
      $response = json_decode($raw, true);
    else
      $response = $raw;
    
    $this->success = true;
    return $response;
  }
}
