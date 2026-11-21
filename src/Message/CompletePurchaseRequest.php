<?php

namespace Omnipay\Iyzico\Message;

/**
 * Dönüş: iyzico tarayıcıyı callbackUrl'e yalnız "token" ile POST eder. Sonuç bu jetonla SUNUCUDAN sorgulanır
 * (checkoutform/auth/ecom/detail). Jeton parametre olarak verilmezse istekten okunur.
 * Doğrulama: yanıt başarılı + paymentStatus SUCCESS + fraudStatus 1; verilmişse basketId = transactionId ve
 * paidPrice ≥ amount (taksit vade farkı paidPrice'ı büyütebilir).
 */
class CompletePurchaseRequest extends AbstractRequest
{
    public function getToken() { return $this->getParameter('token'); }
    public function setToken($value) { return $this->setParameter('token', $value); }

    public function getData(): array
    {
        $token = (string) ($this->getToken() ?: $this->httpRequest->request->get('token', $this->httpRequest->query->get('token', '')));
        if ($token === '') {
            throw new \Omnipay\Common\Exception\InvalidRequestException('The token parameter is required');
        }

        return ['locale' => (string) ($this->getLocale() ?: 'tr'), 'conversationId' => (string) ($this->getTransactionId() ?? ''), 'token' => $token];
    }

    public function sendData($data): CompletePurchaseResponse
    {
        return $this->response = new CompletePurchaseResponse($this, $this->call('/payment/iyzipos/checkoutform/auth/ecom/detail', $data));
    }
}
