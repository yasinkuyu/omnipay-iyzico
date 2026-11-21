<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\NotificationInterface;

/**
 * Ödeme formunun sonucu (sunucudan sorgulanmış). isVerified(): yanıt okundu, başarılı ve (verilmişse) sipariş no / tutar
 * tutuyor. isSuccessful(): doğrulandı + paymentStatus SUCCESS + fraudStatus 1.
 */
class CompletePurchaseResponse extends ApiResponse implements NotificationInterface
{
    public function isVerified(): bool
    {
        if (!parent::isSuccessful()) {
            return false;
        }
        $req = $this->getRequest();
        if ($req->getTransactionId() !== null && (string) ($this->data['basketId'] ?? '') !== (string) $req->getTransactionId()) {
            return false;
        }
        if ($req->getParameters()['amount'] ?? null) {
            if ((float) ($this->data['paidPrice'] ?? 0) + 0.001 < (float) $req->getAmount()) {
                return false;
            }
        }

        return true;
    }

    public function isSuccessful(): bool
    {
        return $this->isVerified() && ($this->data['paymentStatus'] ?? '') === 'SUCCESS' && (int) ($this->data['fraudStatus'] ?? 1) === 1;
    }

    /** Dolandırıcılık incelemesinde (fraudStatus 0): sonuç sonra kesinleşir */
    public function isPendingReview(): bool
    {
        return $this->isVerified() && ($this->data['paymentStatus'] ?? '') === 'SUCCESS' && (int) ($this->data['fraudStatus'] ?? 1) === 0;
    }

    public function getTransactionStatus(): string
    {
        if (!$this->isVerified() || $this->isPendingReview()) {
            return self::STATUS_PENDING;
        }

        return $this->isSuccessful() ? self::STATUS_COMPLETED : self::STATUS_FAILED;
    }

    public function getTransactionId(): ?string
    {
        return ($this->data['basketId'] ?? '') !== '' ? (string) $this->data['basketId'] : null;
    }

    public function getMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        }
        if ($this->isPendingReview()) {
            return 'Payment is under fraud review';
        }
        $m = parent::getMessage();
        if ($m === null && ($this->data['paymentStatus'] ?? '') === 'FAILURE') {
            return trim((string) ($this->data['errorMessage'] ?? '')) ?: 'Payment failed';
        }

        return $m;
    }

    /** Sepet tutarı (price) */
    public function getAmount(): ?string
    {
        return isset($this->data['price']) ? number_format((float) $this->data['price'], 2, '.', '') : null;
    }

    /** Müşterinin ödediği (vade farkı dahil) */
    public function getPaidAmount(): ?string
    {
        return isset($this->data['paidPrice']) ? number_format((float) $this->data['paidPrice'], 2, '.', '') : null;
    }

    public function getInstallment(): int
    {
        return max(1, (int) ($this->data['installment'] ?? 1));
    }

    public function getMaskedCard(): ?string
    {
        $l4 = (string) ($this->data['lastFourDigits'] ?? '');

        return $l4 !== '' ? trim(($this->data['binNumber'] ?? '') . '******' . $l4) : null;
    }

    public function getCardFamily(): ?string
    {
        return ($this->data['cardFamily'] ?? '') !== '' ? (string) $this->data['cardFamily'] : null;
    }
}
