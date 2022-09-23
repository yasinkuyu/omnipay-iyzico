<?php  
 
 include "options.php";

 $response = $gateway->checkout(
 [
     'enabled_installments' => array(1,2,3,4,5,6,7,8,9),
     'conversationId'=> '123123', # replace conversationId
     'amount'        => 17.00,
     'currency'      => 'TRY',
     'card'          => $options,

     'secure3d'      => true,
     'identityNumber'=> '123456789011', // TC Number

     'returnUrl'     => "http://local.iyzico/checkout_callback.php",

     'items' => array(
         array(
             'id' => 1,
             'name' => 10,
             'price' => 17.00, // product totals must be equal to *amount*
             'description' => 'Product 1 Desc',
             'quantity' => 2
         )
     ),  
 ]
 )->send();
 
 if ($response->isSuccessful()) {

    echo " conversationId: " . $response->getConversationId() . PHP_EOL;
    echo " token: " . $response->getToken(). PHP_EOL;

    echo $response->getMessage(). PHP_EOL;
    echo $response->getCheckoutFormContent(); 
    // echo $response->getPayWithIyzicoPageUrl();
    // echo $response->getPaymentPageUrl();

    // responsive
    echo '<div id="iyzipay-checkout-form" class="responsive"></div>';

    // popup
    // echo '<div id="iyzipay-checkout-form" class="popup"></div>';

 }else{
     echo $response->getError();
 } 
 
 // var_dump($response);