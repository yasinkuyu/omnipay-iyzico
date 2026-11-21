<?php

namespace Omnipay\Iyzico;

use Omnipay\Common\AbstractGateway;
use Omnipay\Iyzico\Message\CompletePurchaseRequest;
use Omnipay\Iyzico\Message\FetchTransactionRequest;
use Omnipay\Iyzico\Message\PurchaseRequest;
use Omnipay\Iyzico\Message\RefundRequest;
use Omnipay\Iyzico\Message\VoidRequest;

/**
 * iyzico Ödeme Formu (Checkout Form), SDK'sız: istekler IYZWSv2 ile imzalanır.
 *
 * purchase()          ödeme formunu başlatır; yanıt müşteriyi iyzico'nun ödeme sayfasına GET ile götürür.
 * completePurchase()  dönüşte gelen jetonla sonucu iyzico'dan SUNUCUDAN sorgular (tarayıcıdan gelen bilgiye güvenilmez).
 * void()              ödemeyi iptal eder (aynı gün, tam tutar); refund() kalem (paymentTransactionId) bazında iade.
 * fetchTransaction()  ödeme ayrıntısı.
 *
 * (c) Yasin Kuyu — https://github.com/yasinkuyu/omnipay-iyzico
 */
class Gateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'Iyzico';
    }

    public function getDefaultParameters(): array
    {
        return ['apiKey' => '', 'secretKey' => '', 'testMode' => false];
    }

    public function getApiKey() { return $this->getParameter('apiKey'); }
    public function setApiKey($value): self { return $this->setParameter('apiKey', $value); }

    public function getSecretKey() { return $this->getParameter('secretKey'); }
    public function setSecretKey($value): self { return $this->setParameter('secretKey', $value); }

    public function purchase(array $options = []): PurchaseRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    public function completePurchase(array $options = []): CompletePurchaseRequest
    {
        return $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    public function void(array $options = []): VoidRequest
    {
        return $this->createRequest(VoidRequest::class, $options);
    }

    public function refund(array $options = []): RefundRequest
    {
        return $this->createRequest(RefundRequest::class, $options);
    }

    public function fetchTransaction(array $options = []): FetchTransactionRequest
    {
        return $this->createRequest(FetchTransactionRequest::class, $options);
    }
}
