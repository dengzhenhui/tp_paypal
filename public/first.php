<?php

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

require __DIR__  . '/../vendor/autoload.php';
$apiContext = new ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'ASJYmKgE-42aL2hLbTZdKQ5ZawamATKaN_mQr2QepArz0zKHnevSlWpPZbdvqsthYebF80OuXTDu4_lh',     // ClientID
        'EBaKA4tsh32VG9PFbyYDrNlfOqsEoTIN8XX_uELssQXWLWm7P8SGcisUmSPhNaxx-1ICgrU3bHPQxj_f'      // ClientSecret
    )
);

// After Step 2
$payer = new Payer();
$payer->setPaymentMethod('paypal');

$amount = new Amount();
$amount->setTotal('1.00');
$amount->setCurrency('USD');

$transaction = new Transaction();
$transaction->setAmount($amount);

$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("https://example.com/your_redirect_url.html")
    ->setCancelUrl("https://example.com/your_cancel_url.html");

$payment = new Payment();
$payment->setIntent('sale')
    ->setPayer($payer)
    ->setTransactions(array($transaction))
    ->setRedirectUrls($redirectUrls);
// After Step 3
try {
    $payment->create($apiContext);
    echo "\n\nRedirect user to approval_url: " . $payment->getApprovalLink() . "\n";
}
catch (PayPalConnectionException $ex) {
    // This will print the detailed information on the exception.
    //REALLY HELPFUL FOR DEBUGGING
    echo $ex->getData();
}