 <?php  
 
    include "options.php";
 
    $response = $gateway->purchase(
    [
        'installment'   => 4,
        'orderId'       => '12345', # conversationId
        'amount'        => 6.00,
        'currency'      => 'TRY',
        'card'          => $options,

        '3dSecure'      => true,
        'identityNumber'=> '123456789011', // TC Number

        'returnUrl'     => "http://local.desktop/callback.php",

        'items' => array(
            array(
                'id' => 1,
                'name' => 10,
                'price' => 6.00, // product totals must be equal to *amount*
                'description' => 'Product 1 Desc',
                'quantity' => 2
            )
        ),  
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