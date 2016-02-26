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
        // iterate over all of your stores
        foreach($dpd->listStorefronts() as $storefront) {
            // Iterate over your stores' products
            foreach($dpd->listProducts($storefront["id"]) as $product)
            {
              // Do something with $product;
            }
        }
    } catch(GetDPDApiError $e) {
      die("Error! ".$e);
    }
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

### Verifying A Notification

You can verify a notification from the URL integration by using the
`verifyNotification` method.

    <?php
    $dpd->verifyNotification($_POST);
    ?>

It will return either `VERIFIED` on success or `INVALID` if the
notification did not come from GetDPD.

### Sample IPN handler

    <?php
    /*
        dpd_ipn_notify.php
        Sample notification script to use with DPD's Notification URL integration.
        This script will show you how to use the GetDPDApi to download the details
        of a purchase POSTed to your notification URL. You can use that data to
        record in your own database, register background processes, subscribe your
        customers to an email list, etc.
    */
    require_once "GetDPDApi.php";
    // Fill in your credentials from the DPD Access information from the bottom
    // of this page: https://getdpd.com/user/profile
    $dpd = new GetDPDApi("<your username>", "<your api key>");
    try {
        // Verify the POST parameters to make sure we're dealing with a valid
        // purchase notification
        if($dpd->verifyNotification($_POST) !== 'VERIFIED')
            die("Error: notification didn't come from getdpd.com");
        // Look up purchase details txn_id is POSTed to the script.
        // Documentation to posted variables:
        // http://support.getdpd.com/hc/en-us/articles/201282853-IPN-Notification-URL
        $purchase = $dpd->getPurchase(null, $_POST["txn_id"]);
        // Do something with the purchase here:
        var_export($purchase);
    } catch(GetDPDApiError $e) {
        die("Error! ".$e->getMessage());
    }