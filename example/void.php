<?php  
 
    include "options.php";

    $response = $gateway->void(
    [
        'conversationId'    => $_GET['conversationId'], // orderid kullanılabilir
        'paymentId'         => $_GET['paymentId'],
        'card'              => $options
    ]
    )->send();

    if ($response->isSuccessful()) {
        echo " conversationId: " . $response->getConversationId() . PHP_EOL;
        echo " paymentId: " . $response->getPaymentId(). PHP_EOL;
        echo " paymentTransactionId: " . $response->getPaymentTransactionId(). PHP_EOL;
        echo $response->getMessage(). PHP_EOL;
    }else{
        echo $response->getError();
    } 
 
    // var_dump($response);