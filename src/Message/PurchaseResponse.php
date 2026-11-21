<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/** Ödeme formu açıldıysa iyzico ödeme sayfasına GET yönlendirmesi; jeton dönüşte sonucu sorgulamak için saklanır. */
class PurchaseResponse extends ApiResponse implements RedirectResponseInterface
{
    public function isSuccessful(): bool
    {
        return false;
    }

    public function isRedirect(): bool
    {
        return parent::isSuccessful() && !empty($this->data['paymentPageUrl']) && !empty($this->data['token']);
    }

    public function getRedirectUrl(): ?string
    {
        return $this->isRedirect() ? (string) $this->data['paymentPageUrl'] : null;
    }

    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    public function getRedirectData(): array
    {
        return [];
    }

    public function getToken(): ?string
    {
        return $this->isRedirect() ? (string) $this->data['token'] : null;
    }

    public function getMessage(): ?string
    {
        return $this->isRedirect() ? null : parent::getMessage();
    }
}
