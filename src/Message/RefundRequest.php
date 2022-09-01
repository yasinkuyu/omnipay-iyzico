<?php

namespace Omnipay\Iyzico\Message;

/**
 * Iyzico Purchase Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class RefundRequest extends PurchaseRequest {
    
    protected $actionType = 'refund';
 
    public function sendData($data) {
         
        $mode = $this->getTestMode() ? "test" : "live";

        $options = new \Iyzipay\Options();
        $options->setApiKey($this->getApiId());
        $options->setSecretKey($this->getSecretKey());
        $options->setBaseUrl($this->endpoints[$mode]);

        $request = new \Iyzipay\Request\CreateRefundRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($this->getConversationId());
        $request->setPaymentTransactionId($this->getPaymentTransactionId());
        $request->setPrice($this->getAmount());

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
        
        $request->setIp($this->getClientIp());

        $data = \Iyzipay\Model\Refund::create($request, $options);

        $this->response = new Response($this, $data);

        return $this->response;

    }
}
