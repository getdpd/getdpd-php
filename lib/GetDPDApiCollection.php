<?php

/*
 * This file is part of the DPD API package.
 * (c) 2010-2016 Portal Labs, LLC <contact@portallabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'GetDPDApiHelper.php';

class GetDPDApiCollection implements Iterator, Countable
{
  public
    $method = 'GET';

  protected
    $api,
    $uri,
    $parameters,
    $paging,
    $page,
    $response,
    $total_count,
    $current,
    $key;

  public function __construct(GetDPDApiHelper $api, $action, $parameters = array())
  {
    $this->api = $api;
    $this->action = $action;
    $this->parameters = $parameters;

    // paging member indicates that we're iterating over paged resources; not that we're calling
    // paged resources. all collections are paged now
    $this->paging = !isset($this->parameters['page']);
  }

  // Implements Iterator
  public function rewind()
  {
    $this->page = $this->paging ? 1 : intval($this->paramaters['page']);
    $this->response = $this->callAPI();
    $this->key = 0;
    $this->current = each($this->response);
  }

  public function current()
  {
    return $this->current['value'];
  }

  public function key()
  {
    return $this->key;
  }

  public function next()
  {
    $this->key++;
    $this->current = each($this->response);
    if(!$this->current && $this->paging)
    {
      $this->page++;
      $this->response = $this->callAPI();
      $this->current = each($this->response);
    }
  }

  public function valid()
  {
    return $this->current;
  }


  // Implements Countable
  public function count()
  {
    if($this->response === null)
      $this->rewind();

    return $this->total_count;
  }

  protected function callAPI()
  {
    $parameters = $this->parameters;
    if($this->paging)
      $parameters['page'] = $this->page;

    try {
      $response = $this->api->call($this->method, $this->action, $parameters);
    }
    catch(GetDPDResourceNotFoundError $e) {
      $response = array();
    }

    $headers = $this->api->getHeaders();
    if(isset($headers['Dpd-Collection-Count']))
      $this->total_count = $headers['Dpd-Collection-Count'];

    return $response;
  }

}
