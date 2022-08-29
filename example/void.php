<?php  
 
    include "options.php";

    $response = $gateway->void(
    [
        'orderId'       => '12345', # conversationId
        'transId'       => '18177789',
        'card'          => $options
    ]
    )->send();

    if ($response->isSuccessful()) {
        echo "conversationId: " . $response->getOrderId() . PHP_EOL;
        echo "paymentId: " . $response->getTransactionReference(). PHP_EOL;
        echo $response->getMessage(). PHP_EOL;
    }else{
        echo $response->getError();
    } 
 
    // var_dump($response);