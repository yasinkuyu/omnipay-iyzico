<?php

namespace Omnipay\Iyzico;

use Omnipay\Common\AbstractGateway;

/**
 * Iyzico Gateway
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class Gateway extends AbstractGateway {

    public function getName() {
        return 'Iyzico';
    }

    public function getDefaultParameters() {
        return array(
            'apiid' => '',
            'secretkey' => '',
            'installment' => '',
            'token' => '',
            'enabled_installments' => array(2, 3, 6, 9),
            'currency' => 'TRY',
            'orderId' => rand(),
            'testMode' => false,
            'secure3d' => true,
            'identityNumber' => '',
            'returnUrl' => 'http://google.com'
        );
    }

    public function authorize(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\AuthorizeRequest', $parameters);
    }

    public function capture(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\CaptureRequest', $parameters);
    }

    public function purchase(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\PurchaseRequest', $parameters);
    }

    public function checkout(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\CheckoutRequest', $parameters);
    }

    public function checkout_status(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\CheckoutStatusRequest', $parameters);
    }

    public function refund(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\RefundRequest', $parameters);
    }

    public function void(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Iyzico\Message\VoidRequest', $parameters);
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

    public function getToken() {
        return $this->getParameter('token');
    }

    public function setToken($value) {
        return $this->setParameter('token', $value);
    }

    public function getIdentityNumber() {
        return $this->getParameter('identityNumber');
    }

    public function setIdentityNumber($value) {
        return $this->setParameter('identityNumber', $value);
    }

    public function getInstallment() {
        return $this->getParameter('installment');
    }

    public function setInstallment($value) {
        return $this->setParameter('installment', $value);
    }

    public function getEnabledInstallments() {
        return $this->getParameter('enabled_installments');
    }

    public function setEnabledInstallments($value) {
        return $this->setParameter('enabled_installments', $value);
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
