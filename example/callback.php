<?php 

include "vendor/autoload.php";

$response = $_POST;

print_r($response);

if(isset($response->conversationId)){
    echo "conversationId: ". $response->conversationId; # Required to use error mapping.
}

if(isset( $response->paymentId)){
    echo "paymentId:" . $response->paymentId; # Required for use in cancellation.
}

?>
<br>
<br>
<a href="/purchase.php">new purchase</a>