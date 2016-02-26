<?php

/*
 * This file is part of the DPD API package.
 * (c) 2010-2012 Portal Labs, LLC <contact@portallabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'GetDPDApiHelper.php';

class GetDPDApi
{
  const VERSION = '2.0';
  
  protected $api = null;
  
  public function __construct($user, $key)
  {
    $this->api = new GetDPDApiHelper($user, $key);
  }
  
  public function ping()
  {
    return $this->api->get('/');
  }
  
  public function listStorefronts()
  {
    return $this->api->getCollection('/storefronts');
  }
  
  public function getStorefront($id)
  {

    return $this->api->get("/storefronts/{$id}");
  }
  
  public function listProducts($storefront_id=null)
  {
    return $this->api->getCollection('/products', array('storefront_id' => $storefront_id));
  }
  
  public function getProduct($id)
  {
    return $this->api->get("/products/{$id}");
  }
  
  public function listPurchases($params=array())
  {
    return $this->api->getCollection("/purchases", $params);
  }

  public function getPurchase($website_id, $id)
  {
    return $this->api->get("/purchases/{$id}");
  }

  public function listSubscribers($storefront_id, $params=array())
  {
    return $this->api->getCollection("/storefronts/{$storefront_id}/subscribers", $params);
  }
  
  public function getSubscriber($storefront_id, $id)
  {
    return $this->api->get("/storefronts/{$storefront_id}/subscribers/{$id}");
  }

  public function listCustomers($params=array())
  {
    return $this->api->getCollection("/customers", $params);
  }
  
  public function getCustomer($id)
  {
    return $this->api->get("/customers/{$id}");
  }

  public function verifySubscriber($storefront_id, $params)
  {
    return $this->api->get("/storefronts/{$storefront_id}/subscribers/verify", $params);
  }

  public function verifyNotification($params)
  {
    return $this->api->call('POST', "/notification/verify", $params);
  }

  // This is here for compatibility with the legacy version
  public function doApiRequest($action, $params, $method='GET')
  {
    return $this->api->call($method, $action, $params);
  }

  public static function developmentFactory($user, $key, $development_uri)
  {
    $instance = new self($user, $key);
    $instance->api = new GetDPDApiHelper($user, $key, $development_uri);
    return $instance;
  }
}
