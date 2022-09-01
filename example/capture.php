<?php  
 
    include "options.php";

    // If there is a transaction initiated with 3d, capture (complete) the payment.
    $response = $gateway->capture(
    [
        'conversationId'=> $_GET['conversationId'], // orderid kullanılabilir
        'paymentId'     => $_GET['paymentId'],
        'card'          => $options
    ]
    )->send();

    if ($response->isSuccessful()) {
        echo "conversationId: " . $response->getConversationId() . PHP_EOL;
        echo "paymentId: " . $response->getPaymentId(). PHP_EOL;
        echo "paymentTransactionId: " . $response->getPaymentTransactionId(). PHP_EOL;
        echo $response->getMessage(). PHP_EOL;
    }else{
        echo $response->getError();
    } 

    // var_dump($response);