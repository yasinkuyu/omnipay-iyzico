<?php

namespace Omnipay\Iyzico\Message;

/**
 * Iyzico Void Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class VoidRequest extends PurchaseRequest {
    
    protected $actionType = 'CC.RV';
   
}
