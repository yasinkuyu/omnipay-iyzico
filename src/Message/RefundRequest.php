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

}
