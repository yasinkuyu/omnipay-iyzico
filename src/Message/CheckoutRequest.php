<?php

namespace Omnipay\Iyzico\Message;

/**
 * Iyzico Checkout Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class CheckoutRequest extends PurchaseRequest {
    protected $actionType = 'checkout';
 
    public function sendData($data) {

        $this->validate('card');
        $this->getCard()->validate();

        $card = $this->getCard();
        $mode = $this->getTestMode() ? "test" : "live";

        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiId());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->endpoints[$mode]);

        $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
        $request->setEnabledInstallments($this->getEnabledInstallments());

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

        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

        if($this->getSecure3d()){
            $request->setCallbackUrl($this->getReturnUrl());
        }

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
        
        $data = \Iyzipay\Model\CheckoutFormInitialize::create($request, $options);

        $this->response = new Response($this, $data);
 
        return $this->response;

    }

}
