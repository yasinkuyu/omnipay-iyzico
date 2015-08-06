<?php

namespace Omnipay\Iyzico\Message;

/**
 * Iyzico Complete Capture Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class CaptureRequest extends PurchaseRequest {

    protected $actionType = 'CC.DB';

}
