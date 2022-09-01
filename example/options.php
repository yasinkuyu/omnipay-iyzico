<?php

include "../../../../vendor/autoload.php";

use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');
$gateway->setApiId("sandbox-ralKbNz1XYUNjGhAMdLJCHWU1jQgcWsB");
$gateway->setSecretKey("sandbox-qCyKkrSSlyQekZUbpVB8TH5SESq3VWdo");

$gateway->setTestMode(true); // sandbox mode

$options = [
    'number'        => '5526080000000006',
    'expiryMonth'   => '10',
    'expiryYear'    => '2023',
    'cvv'           => '000',
    'firstName'     => 'Yasin',
    'lastName'      => 'Kuyu',
    'email'         => 'yasin@sample.com',

    'city'          => 'Tekirdag',
    'country'       => 'Turkey',
    'address1'      => 'Deneme adres',
];