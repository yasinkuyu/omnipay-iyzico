<?php

namespace Omnipay\Iyzico\Message;

/** İptal (aynı gün, tamamı; ekstrede görünmez): transactionReference (paymentId), clientIp */
class VoidRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('transactionReference');

        return ['locale' => (string) ($this->getLocale() ?: 'tr'), 'conversationId' => (string) ($this->getTransactionId() ?? ''),
            'paymentId' => (string) $this->getTransactionReference(), 'ip' => (string) ($this->getClientIp() ?: '127.0.0.1')];
    }

    public function sendData($data): ApiResponse
    {
        return $this->response = new ApiResponse($this, $this->call('/payment/cancel', $data));
    }
}
