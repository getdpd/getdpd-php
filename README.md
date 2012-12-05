# GetDPD API

The DPD API allows you to easily integrate your website with DPD.

## What's Included

The API is read-only. It gives you access to storefronts, products, purchases, and subscribers.

## Requirements

PHP 5.2 or better with the cURL extension.

## Usage

    <?php
    require_once "GetDPDApi.php";
    
    $dpd = new GetDPDApi("your username", "your key");
    try {
      $storefronts = $dpd->getStorefronts();
      $products = $dpd->listProducts($storefronts[0]["id"]);
    } catch(GetDPDApiError $e) {
      die("Error! ".$e);
    }
    
    var_export($products);
    ?>

See the [full reference](http://getdpd.com/docs/api/index.html) ([pdf version](http://getdpd.com/docs/api/DPDAPIReference.pdf))
for a full reference.

### Handling Errors

A 404 Not Found error will throw a `GetDPDResourceNotFoundError`. A 401 Unauthorized error will throw a `GetDPDAuthenticationError`.
If there is a cURL error or a non-200 HTTP response, the library will throw a `GetDPDApiError` with the cURL error message
or the HTTP status code.

### Verifying A Subscriber

You can verify a subscriber's subscription status with the `verifySubscriber` methods. Pass in either a `username` or `id`.

    <?php
    $storefront_id = 1234;
    $dpd->verifySubscriber($storefront_id, array("id" => 123));
    ?>

or

    <?php
    $storefront_id = 1234;
    $dpd->verifySubscriber($storefront_id, array('username' => 'example@example.com'));
    ?>

Both calls return the subscriber's status. Any subscriber with the `TRIAL`, `ACTIVE` or `CANCELED` status has access to the
subscriber area in your DPD account.
