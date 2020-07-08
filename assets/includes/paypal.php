<?php
require 'assets/import/PayPal/vendor/autoload.php';
$paypal = new \PayPal\Rest\ApiContext(
  new \PayPal\Auth\OAuthTokenCredential(
    $music->config->paypal_id,
    $music->config->paypal_secret
  )
);

$paypal->setConfig(
    array(
      'mode' => $music->config->paypal_mode
    )
);