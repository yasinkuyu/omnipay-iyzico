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

    protected $actionType = 'CC.DB';
    protected $endpoints = [
        'token' => 'https://api.iyzico.com/v2/create',
        'purchase' => 'https://iyziconnect.com/pay-with-transaction-token/',
        'refund' => 'https://api.iyzico.com/v2/refund',
        'status' => 'https://api.iyzico.com/getStatus'
    ];

    public function getData() {

        $this->validate('card');
        $this->getCard()->validate();

        $card = $this->getCard();

        $data = array(
            'mode' => $this->getTestMode() ? "test" : "live",
            'api_id' => $this->getApiId(),
            'secret' => $this->getSecretKey(),
            'installment' => true,
            'external_id' => $this->getOrderId(),
            'transaction_id' => $this->getTransId(),
            'type' => $this->actionType,
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

        if ($this->actionType === "CC.RF") {

            $httpResponse = $this->httpClient->post(
                            $this->endpoints['refund'], 
                            $headers, 
                            $data
                    )->send();

            return $this->response = new Response($this, $httpResponse->getBody());
            
        } else {


            $httpResponse = $this->httpClient->post(
                            $this->endpoints['token'], $headers, $data
                    )->send();

            $token = new Response($this, $httpResponse->getBody());

            $pay = array(
                'card_number' => $this->getCard()->getNumber(),
                'card_expiry_month' => $this->getCard()->getExpiryMonth(),
                'card_expiry_year' => $this->getCard()->getExpiryYear(),
                'card_verification' => $this->getCard()->getCvv(),
                'card_holder_name' => '',
                'card_brand' => $this->getCardProvider($this->getCard()->getNumber()),
                'pay' => 'Ödeme Yap',
                'version' => '1.0',
                'response_mode' => 'SYNC', //todo 3D->ASYNC
                'enable_3d_secure' => false,
                'currency' => $this->getCurrency(),
                'transaction_token' => $token->getCode(),
                'installment-option' => $this->getInstallment(),
                'connector_type' => $this->getBank(),
                'mode' => $this->getTestMode() ? "test" : "live",
            );

            $httpResponsePay = $this->httpClient->post(
                            $this->endpoints['purchase'], $headers, $pay, ['allow_redirects' => false]
                    )->send();

            return $this->response = new Response($this, $httpResponsePay->getBody());
        }
    }

    /**
     * 
     * Get credit cart provider
     * 
     * @param int $cardNumber
     * @return string
     */
    protected function getCardProvider($cardNumber) {
        $brand = "Invalid";
        $digitLength = strlen($cardNumber);
        switch ($digitLength) {
            case 15:
                if (substr($cardNumber, 0, 2) == "34" ||
                        substr($cardNumber, 0, 2) == "37") {
                    $brand = "AMEX";
                }
                break;
            case 13:
                if (substr($cardNumber, 0, 1) == "4") {
                    $brand = "VISA";
                }
                break;
            case 16:
                if (substr($cardNumber, 0, 1) == "4") {
                    $brand = "VISA";
                } else if (substr($cardNumber, 0, 4) == "6011") {
                    $brand = "DISCOVER";
                } else if (intval(substr($cardNumber, 0, 2)) >= 51 &&
                        intval(substr($cardNumber, 0, 2)) <= 55) {
                    $brand = "MASTER";
                }
                break;
        }
        return $brand;
    }

    public function getBank() {
        return $this->getParameter('bank');
    }

    public function setBank($value) {
        return $this->setParameter('bank', $value);
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
