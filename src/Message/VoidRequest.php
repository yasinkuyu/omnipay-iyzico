<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Iyzico Void Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class VoidRequest extends PurchaseRequest {
    
    protected $actionType = 'void';

    public function sendData($data) {
        
        $mode = $this->getTestMode() ? "test" : "live";

        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiId());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->endpoints[$mode]);

        $request = new \Iyzipay\Request\CreateCancelRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($this->getOrderId());
        $request->setPaymentId($this->getTransId());
        $request->setIp($this->getClientIp());

        $data = \Iyzipay\Model\Cancel::create($request, $options);
         
        $this->response = new Response($this, $data);

        return $this->response;

    }
 
}
