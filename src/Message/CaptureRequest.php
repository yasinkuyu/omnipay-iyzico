<?php

namespace Omnipay\Iyzico\Message;

/**
 * Iyzico Complete Capture 3d Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class CaptureRequest extends PurchaseRequest {
    protected $actionType = 'capture';

    public function sendData($data) {
        
        $mode = $this->getTestMode() ? "test" : "live";

        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiId());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->endpoints[$mode]);

        $request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($this->getConversationId());
        $request->setPaymentId($this->getPaymentId());
        
        $data = \Iyzipay\Model\ThreedsPayment::create($request, $options);
         
        $this->response = new Response($this, $data);

        return $this->response;

    }
 
}
