<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Iyzico Purchase Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class PurchaseRequest extends AbstractRequest {
    
    protected $endpoints = [
        'purchase' => 'https://api.iyzico.com/v2/create',
        'status' => 'https://api.iyzico.com/getStatus'
    ];
 
    public function getData() {

        $this->validate('amount', 'card');
        $this->getCard()->validate();

        $card = $this->getCard();

        $data = array(
            'api_id' => $this->getApiId(),
            'secret' => $this->getSecretKey(),
            'external_id' => $this->getTransId(),
            'mode' => $this->getTestMode() ? "test" : "live",
            'installment' => true,
            
            'type' => "CC.DB",
            'return_url' => $this->getReturnUrl(),
            'amount' => $this->getAmountInteger(),
            'currency' => $this->getCurrency(),
            'descriptor' => $this->getDescription(),
            
            'customer_first_name' => $card->getBillingFirstName(),
            'customer_last_name' => $card->getBillingLastName(),
            'customer_company_name' => $card->getCompany(),
            
            'customer_shipping_address_line_1' => $card->getShippingAddress1(),
            'customer_shipping_address_line_2' => $card->getShippingAddress2(),
            'customer_shipping_address_zip' => $card->getShippingPostcode(),
            'customer_shipping_address_city' => $card->getShippingCity(),
            'customer_shipping_address_state' => $card->getShippingState(),
            'customer_shipping_address_country' => $card->getShippingCountry(),
            
            'customer_billing_address_line_1' => $card->getBillingAddress1(),
            'customer_billing_address_line_2' => $card->getBillingAddress2(),
            'customer_billing_address_zip' => $card->getBillingPostcode(),
            'customer_billing_address_city' => $card->getBillingCity(),
            'customer_billing_address_state' => $card->getBillingState(),
            'customer_billing_address_country' => $card->getCountry(),
            
            'customer_contact_phone' => $card->getBillingPhone(),
            'customer_contact_mobile' => $card->getBillingPhone(),
            'customer_contact_email' => $card->getEmail(),
            'customer_contact_ip' => $this->getClientIp(),
            'customer_language' => 'tr',
            
        );

        $data += array(
            'card_number' => $card->getNumber(),
            'card_expiry_month' => $card->getExpiryMonth(),
            'card_expiry_year' => $card->getExpiryYear(),
            'card_verification' => $card->getCvv(),
            'card_holder_name' => '',
            'card_brand' => $this->findCardBrand($card->getNumber()),
        );
        
        // List products
        $items = $this->getItems();
        if (!empty($items)) {
            foreach ($items as $key => $item) {

                $data += array(
                    'item_id_[' . $key . ']' => $item->getIterator(),
                    'item_name_1[' . $key . ']' => $item->getName(),
                    'item_unit_quantity_[' . $key . ']' => 1,
                    'item_unit_amount_[' . $key . ']' => $item->getPrice()
                );
                
            }
        }
        
        return $data;
    }

    public function sendData($data) {

        // Post to Iyzico
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        );

        // Register the payment
        $this->httpClient->setConfig(array(
            'curl.options' => array(
                'CURLOPT_SSL_VERIFYHOST' => 2,
                'CURLOPT_SSLVERSION' => 0,
                'CURLOPT_SSL_VERIFYPEER' => 0,
                'CURLOPT_RETURNTRANSFER' => 1,
                'CURLOPT_POST' => 1
            )
        ));
            
        $httpResponse = $this->httpClient->post($this->endpoints['purchase'], $headers, $data)->send();

        return $this->response = new Response($this, $httpResponse->getBody());
    }
    
    
    protected function findCardBrand($cardNumber) {
        $brand = "Invalid";
        $digitLength = strlen($cardNumber);
        switch ($digitLength) {
            case 15:
                if(substr($cardNumber, 0, 2) == "34" || substr($cardNumber, 0, 2) == "37") {
                    $brand = "AMEX";
                }
                break;
            case 13:
                if(substr($cardNumber, 0, 1) == "4") {
                    $brand = "VISA";
                }
                break;
            case 16:
                if(substr($cardNumber, 0, 1) == "4") {
                    $brand = "VISA";
                } else if(substr($cardNumber, 0, 4) == "6011") {
                    $brand = "DISCOVER";
                } else if(intval(substr($cardNumber, 0, 2)) >= 51 && intval(substr($cardNumber, 0, 2)) <= 55) {
                    $brand = "MASTER";
                }
                break;
           
        }
        return $brand;
    }
    
    public function getApiId() {
        return $this->getParameter('apiId');
    }

    public function setApiId($value) {
        return $this->setParameter('apiId', $value);
    }

    public function getSecretKey() {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value) {
        return $this->setParameter('secretKey', $value);
    }

    public function getInstallment() {
        return $this->getParameter('installment');
    }

    public function setInstallment($value) {
        return $this->setParameter('installment', $value);
    }

    public function getType() {
        return $this->getParameter('type');
    }

    public function setType($value) {
        return $this->setParameter('type', $value);
    }

    public function getOrderId() {
        return $this->getParameter('orderid');
    }

    public function setOrderId($value) {
        return $this->setParameter('orderid', $value);
    }

    public function getTransId() {
        return $this->getParameter('transId');
    }

    public function setTransId($value) {
        return $this->setParameter('transId', $value);
    }

}
