<?php

namespace Omnipay\Iyzico\Message;

/**
 * Iyzico Checkout Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class CheckoutStatusRequest extends PurchaseRequest {
    protected $actionType = 'checkout_status';
 
    public function sendData($data) {

        $mode = $this->getTestMode() ? "test" : "live";

        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiId());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->endpoints[$mode]);

        $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($this->getConversationId());
        $request->setToken($this->getToken());

        $data = \Iyzipay\Model\CheckoutForm::retrieve($request, $options);

        $this->response = new Response($this, $data);
 
        return $this->response;

    }

}
