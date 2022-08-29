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

    protected $actionType = 'purchase';

    protected $endpoints = [
        'test' => 'https://sandbox-api.iyzipay.com',
        'live' => 'https://api.iyzipay.com',
    ];
  
    public function getData() {

        $this->validate('card');
        $this->getCard()->validate();

        $card = $this->getCard();

        $data = array(
            'external_id' => $this->getOrderId(),
            'transaction_id' => $this->getTransId(),
             
        );
  
        return $data;
    }

    public function sendData($data) {
        
        $this->validate('card');
        $this->getCard()->validate();

        $card = $this->getCard();
        $mode = $this->getTestMode() ? "test" : "live";

        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiId());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->endpoints[$mode]);

        $request = new \Iyzipay\Request\CreatePaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($this->getOrderId()); // $token->getCode()
        $request->setPrice($this->getAmount()); // or getAmountInteger
        $request->setPaidPrice($this->getAmount());

        switch ($this->getCurrency()) {
            case 'USD':
                $request->setCurrency(\Iyzipay\Model\Currency::USD);
                break;
        
            case 'EUR':
                $request->setCurrency(\Iyzipay\Model\Currency::EUR);
                break;
        
            case 'GBP':
                $request->setCurrency(\Iyzipay\Model\Currency::GBP);
                break;
                
            default:
                $request->setCurrency(\Iyzipay\Model\Currency::TL);
                break;
        }

        $request->setInstallment($this->getInstallment());
        $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

        if($this->get3dSecure()){
            $request->setCallbackUrl($this->getReturnUrl());
        }
        
        $paymentCard = new \Iyzipay\Model\PaymentCard();
        $paymentCard->setCardHolderName($this->getCard()->getName());
        $paymentCard->setCardNumber($this->getCard()->getNumber());
        $paymentCard->setExpireMonth($this->getCard()->getExpiryMonth());
        $paymentCard->setExpireYear($this->getCard()->getExpiryYear());
        $paymentCard->setCvc($this->getCard()->getCvv());
        $paymentCard->setRegisterCard(0);
        $request->setPaymentCard($paymentCard);

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($this->getOrderId());
        $buyer->setName($card->getFirstName());
        $buyer->setSurname($card->getLastName());
        $buyer->setGsmNumber($card->getPhone());
        $buyer->setEmail($card->getEmail());
        $buyer->setIdentityNumber($this->getIdentityNumber());
        $buyer->setLastLoginDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationDate(date('Y-m-d H:i:s'));
        $buyer->setRegistrationAddress($card->getAddress1());
        $buyer->setIp($this->getClientIp());
        $buyer->setCity($card->getCity());
        $buyer->setCountry($card->getCountry());
        $buyer->setZipCode($card->getPostcode());
        $request->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($card->getShippingFirstName() . " " . $card->getShippingLastName());
        $shippingAddress->setCity($card->getShippingCity());
        $shippingAddress->setCountry($card->getShippingCountry());
        $shippingAddress->setAddress($card->getShippingAddress1());
        $shippingAddress->setZipCode($card->getShippingPostcode());
        $request->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($card->getBillingFirstName() . " " . $card->getBillingLastName());
        $billingAddress->setCity($card->getBillingCity());
        $billingAddress->setCountry($card->getBillingCountry());
        $billingAddress->setAddress($card->getBillingAddress1());
        $billingAddress->setZipCode($card->getBillingPostcode());
        $request->setBillingAddress($billingAddress);
 
        $basketItems = array();

        // List products
        $items = $this->getItems();

        if (!empty($items)) {
            foreach ($items as $key => $item) {

                $firstBasketItem = new \Iyzipay\Model\BasketItem();
                $firstBasketItem->setId($item->getName());
                $firstBasketItem->setName($item->getName());
                $firstBasketItem->setCategory1("Genel");
                $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                $firstBasketItem->setPrice($item->getPrice());
                
                $basketItems[] = $firstBasketItem;

            }
        }

        $request->setBasketItems($basketItems);
        
        if($this->get3dSecure()){ //3d
            $data = \Iyzipay\Model\ThreedsInitialize::create($request, $options);
        }else{
            $data = \Iyzipay\Model\Payment::create($request, $options);
        }

        $this->response = new Response($this, $data);

        if($data->getStatus() == "success"){

            // display 3ds form
            if($this->get3dSecure()){
                echo $data->getHtmlContent(); 
            }
        }

        return $this->response;

    }
 
    public function getIdentityNumber() {
        return $this->getParameter('identityNumber');
    }

    public function setIdentityNumber($value) {
        return $this->setParameter('identityNumber', $value);
    }

    public function get3dSecure() {
        return $this->getParameter('3dSecure');
    }

    public function set3dSecure($value) {
        return $this->setParameter('3dSecure', $value);
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
