<?php  
 
    include "options.php";

    $response = $gateway->refund(
    [
        'amount'               => 2.00,
        'conversationId'       => $_GET['conversationId'], // orderid kullanılabilir
        'paymentTransactionId' => $_GET['paymentTransactionId'],
        'card'                 => $options
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