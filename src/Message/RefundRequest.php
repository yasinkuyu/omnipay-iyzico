<?php

namespace Omnipay\Iyzico\Message;

/**
 * İade (kalem bazında): transactionReference = paymentTransactionId (CompletePurchaseResponse::getItemTransactions()),
 * amount (o kalemin ödenen tutarını aşamaz), clientIp. Çok kalemli sepette tutarı kalemlere bölmek çağırana kalır.
 */
class RefundRequest extends AbstractRequest
{
    public function getData(): array
    {
        $this->validate('transactionReference', 'amount');

        return ['locale' => (string) ($this->getLocale() ?: 'tr'), 'conversationId' => (string) ($this->getTransactionId() ?? ''),
            'paymentTransactionId' => (string) $this->getTransactionReference(), 'price' => self::price($this->getAmount()),
            'currency' => $this->currencyCode(), 'ip' => (string) ($this->getClientIp() ?: '127.0.0.1')];
    }

    public function sendData($data): ApiResponse
    {
        return $this->response = new ApiResponse($this, $this->call('/payment/refund', $data));
    }
}
