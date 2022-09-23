<?php

include "../../../../vendor/autoload.php";

use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');
$gateway->setApiId("sandbox-ralKbNz1XYUNjGhAMdLJCHWU1jQgcWsB");
$gateway->setSecretKey("sandbox-qCyKkrSSlyQekZUbpVB8TH5SESq3VWdo");

$gateway->setTestMode(true); // sandbox mode

$options = [
    'number'        => '5526080000000006', // not needed in ***checkout*** mode. should not be left blank.
    'expiryMonth'   => '10', // not needed in ***checkout*** mode. should not be left blank.
    'expiryYear'    => '2023', // not needed in ***checkout*** mode. should not be left blank.
    'cvv'           => '000', // not needed in ***checkout*** mode. should not be left blank.
    'firstName'     => 'Yasin',
    'lastName'      => 'Kuyu',
    'email'         => 'yasin@sample.com',

    'city'          => 'Tekirdag',
    'country'       => 'Turkey',
    'address1'      => 'Deneme adres',
];