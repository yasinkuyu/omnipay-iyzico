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
            $this->data = json_decode(strval($data));
        } catch (\Exception $ex) {
            throw new InvalidResponseException();
        }
        echo var_dump($this->data);
    }

    /**
     * Whether or not response is successful
     *
     * @return bool
     */
    public function isSuccessful() {
        return (string) $this->data->response->state === 'success';
    }

    /**
     * Get is redirect
     *
     * @return bool
     */
    public function isRedirect() {
        return false; //todo
    }

    /**
     * Get a code describing the status of this response.
     *
     * @return string|null code
     */
    public function getCode() {
        return $this->isSuccessful() ? $this->data->transaction->external_id : parent::getCode();
    }

    /**
     * Get transaction reference
     *
     * @return string
     */
    public function getTransactionReference() {

        return $this->isSuccessful() ? $this->data->transaction_token: '';
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage() {
        if ($this->isSuccessful()) {
            return $this->data->transaction->state;
        }
        return $this->data->response->error_message;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError() {
        return $this->data->response->error_message;
    }

    /**
     * Get Redirect url
     *
     * @return string
     */
    public function getRedirectUrl() {
        if ($this->isRedirect()) {
            $data = array(
                'TransId' => $this->data->transaction_token
            );
            return $this->getRequest()->getEndpoint() . '/test/index?' . http_build_query($data);
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
