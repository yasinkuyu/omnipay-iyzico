<?php 

include "../../../../vendor/autoload.php";
include "options.php";

$return_data = $_POST;

# print_r($return_data);

$conversationId = "";
$paymentId = "";

if(isset($return_data['conversationId'])){
    $conversationId = $return_data['conversationId'];
    echo " conversationId: ". $return_data['conversationId']; # Required to use error mapping.
}

if(isset( $return_data['paymentId'])){
    $paymentId = $return_data['paymentId'];
    echo " paymentId:" . $return_data['paymentId']; # Required for use in cancellation.
}

echo "<br>";

echo "<a href='/capture.php?paymentId=$paymentId&conversationId=$conversationId'>capture</a>";
echo "<br>";
 