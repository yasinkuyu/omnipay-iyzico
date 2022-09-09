<?php  
 
 include "options.php";

 $response = $gateway->checkout_status(
 [
     'token' => "21772e3b-0eb6-46c2-8499-a40d66253f2e",
     'conversationId'=> '123456',
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