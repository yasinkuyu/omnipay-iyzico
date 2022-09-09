 <?php  
 
    include "options.php";
 
    $response = $gateway->purchase(
    [
        'installment'   => 4,
        'conversationId'=> '123456', # conversationId
        'amount'        => 9.00,
        'currency'      => 'TRY',
        'card'          => $options,

        'secure3d'      => true,
        'identityNumber'=> '123456789011', // TC Number

        'returnUrl'     => "http://local.iyzico/purchase_callback.php",

        'items' => array(
            array(
                'id' => 1,
                'name' => 10,
                'price' => 9.00, // product totals must be equal to *amount*
                'description' => 'Product 1 Desc',
                'quantity' => 2
            )
        ),  
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