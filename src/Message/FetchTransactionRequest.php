<?php

namespace Omnipay\Iyzico\Message;

/** Ödeme ayrıntısı: transactionReference (paymentId) ya da transactionId (conversationId / basketId) */
class FetchTransactionRequest extends AbstractRequest
{
    public function getData(): array
    {
        if (!$this->getTransactionReference()) {
            $this->validate('transactionId');
        }

        return array_filter(['locale' => (string) ($this->getLocale() ?: 'tr'), 'conversationId' => (string) ($this->getTransactionId() ?? ''),
            'paymentId' => (string) ($this->getTransactionReference() ?? ''), 'paymentConversationId' => (string) ($this->getTransactionId() ?? '')], fn ($v) => $v !== '');
    }

    public function sendData($data): ApiResponse
    {
        return $this->response = new ApiResponse($this, $this->call('/payment/detail', $data));
    }
}
