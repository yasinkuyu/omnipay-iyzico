<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Iyzico Response
 *
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class Response extends AbstractResponse implements RedirectResponseInterface {

    /**
     * Constructor
     *
     * @param  RequestInterface         $request
     * @param  string                   $data / response data
     * @throws InvalidResponseException
     */
    public function __construct(RequestInterface $request, $data) {
        $this->request = $request;
        try {

            $this->data = json_decode($data->getRawResult());
        } catch (\Exception $ex) {
            throw new InvalidResponseException();
        }
    }

    /**
     * Whether or not response is successful
     *
     * @return bool
     */
    public function isSuccessful() {
        if (isset($this->data->status))
            return $this->data->status === 'success';
        return false;
    }

    /**
     * Get is action status
     *
     * @return string
     */
    public function getStatus(){
        if (isset($this->data->status))
        return $this->data->status;
    }

    /**
     * Get is payment status
     *
     * @return string
     */
    public function getPaymentStatus(){
        if (isset($this->data->paymentStatus))
        return $this->data->paymentStatus;
    }

    /**
     * Get is html content for payment
     *
     * @return string
     */
    public function getCheckoutFormContent(){
        if (isset($this->data->checkoutFormContent))
        return $this->data->checkoutFormContent;
    }

    /**
     * Get is iyzico payment page url
     *
     * @return string
     */
    public function getPayWithIyzicoPageUrl(){
        if (isset($this->data->payWithIyzicoPageUrl))
        return $this->data->payWithIyzicoPageUrl;
    }

    /**
     * Get is payment page url
     *
     * @return string
     */
    public function getPaymentPageUrl(){
        if (isset($this->data->paymentPageUrl))
        return $this->data->paymentPageUrl;
    }
    
    /**
     * Get is redirect
     *
     * @return bool
     */
    public function isRedirect() {
        return false; 
    }

    /**
     * Get a code describing the status of this response.
     *
     * @return string|null code
     */
    public function getConversationId() {
        return isset($this->data->conversationId) ? $this->data->conversationId : '';
    }
 
    /**
     * Get transaction token
     *
     * @return string
     */
    public function getToken() {
        return isset($this->data->token) ? $this->data->token : '';
    }
 
    /**
     * Get transaction reference
     *
     * @return string
     */
    public function getPaymentId() {
        return isset($this->data->paymentId) ? $this->data->paymentId : '';
    }

    /**
     * Get payment transaction id
     *
     * @return string
     */
    public function getPaymentTransactionId() {
        return isset($this->data->paymentTransactionId) ? $this->data->paymentTransactionId : '';
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage() {
        return isset($this->data->message) ? $this->data->message : $this->getError();
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError() {
        if (isset($this->data->errorMessage))
            return $this->data->errorMessage . " errorCode:" . $this->data->errorCode;
    }

    /**
     * Get Redirect url
     *
     * @return string
     */
    public function getRedirectUrl() {
        if ($this->isRedirect()) {
            $data = array(
                'transId' => $this->data->conversationId
            );
            return $this->getRequest()->getEndpoint() . '?' . http_build_query($data);
        }
    }

    /**
     * Get Redirect method
     *
     * @return POST
     */
    public function getRedirectMethod() {
        return 'POST';
    }

    /**
     * Get Redirect url
     *
     * @return null
     */
    public function getRedirectData() {
        return null;
    }

}
