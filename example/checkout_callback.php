<?php  
 
 include "options.php";

 $response = $gateway->checkout_status(
 [
     'token' => "9ace99f2-2a49-4d25-9018-8f14da58a007",
     'conversationId'=> '123123',
 ]
 )->send();
 
 if ($response->isSuccessful()) {

    echo " conversationId: " . $response->getConversationId() . PHP_EOL;
    echo " token: " . $response->getToken(). PHP_EOL;

    // SUCCESS, FAILURE, INIT_THREEDS, CALLBACK_THREEDS, BKM_POS_SELECTED, CALLBACK_PECCO
    echo $response->getPaymentStatus(). PHP_EOL;
     
 }else{
     echo $response->getError();
 } 
 
 // var_dump($response);