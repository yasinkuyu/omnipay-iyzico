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
        'live' => 'https://api.iyzipay.com'
    ];

    public function getData() {}
 
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
        $request->setConversationId($this->getConversationId());
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

        if($this->getSecure3d()){
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
        $buyer->setId($this->getConversationId());
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
        
        if($this->getSecure3d()){ //3d
            $data = \Iyzipay\Model\ThreedsInitialize::create($request, $options);
        }else{
            $data = \Iyzipay\Model\Payment::create($request, $options);
        }

        $this->response = new Response($this, $data);

        if($data->getStatus() == "success"){

            // display 3ds form
            if($this->getSecure3d()){
                echo $data->getHtmlContent(); 
            }
        }

        return $this->response;

    }
 
    public function getConversationId() {
        return $this->getParameter('conversationId');
    }

    public function setConversationId($value) {
        return $this->setParameter('conversationId', $value);
    }

    public function getPaymentId() {
        return $this->getParameter('paymentId');
    }

    public function setPaymentId($value) {
        return $this->setParameter('paymentId', $value);
    }

    public function getPaymentTransactionId() {
        return $this->getParameter('paymentTransactionId');
    }

    public function setPaymentTransactionId($value) {
        return $this->setParameter('paymentTransactionId', $value);
    }

    public function getIdentityNumber() {
        return $this->getParameter('identityNumber');
    }

    public function setIdentityNumber($value) {
        return $this->setParameter('identityNumber', $value);
    }

    public function getSecure3d() {
        return $this->getParameter('secure3d');
    }

    public function setSecure3d($value) {
        return $this->setParameter('secure3d', $value);
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
 
}
