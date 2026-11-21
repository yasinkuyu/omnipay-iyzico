<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * iyzico yanıtı. isSuccessful(): status = success. isReadable() false ise yanıt okunamadı: işlem gerçekleşmiş olabilir
 * (iptal / iade için "belirsiz", otomatik tekrar denenmemeli; iyzico istekleri yinelemeye dayanıklı değil).
 */
class ApiResponse extends AbstractResponse
{
    public function isReadable(): bool
    {
        return is_array($this->data) && isset($this->data['status']);
    }

    public function isSuccessful(): bool
    {
        return $this->isReadable() && $this->data['status'] === 'success';
    }

    public function getCode(): ?string
    {
        return is_array($this->data) && ($this->data['errorCode'] ?? '') !== '' ? (string) $this->data['errorCode'] : null;
    }

    public function getMessage(): ?string
    {
        if (!$this->isReadable()) {
            return 'Unreadable response';
        }

        return $this->isSuccessful() ? null : (trim((string) ($this->data['errorMessage'] ?? '')) ?: null);
    }

    /** iyzico ödeme numarası (paymentId) */
    public function getTransactionReference(): ?string
    {
        return is_array($this->data) && ($this->data['paymentId'] ?? '') !== '' ? (string) $this->data['paymentId'] : null;
    }

    /** Kalemler: paymentTransactionId => ödenen tutar (vade farkı payı dahil); kalem bazında iade için */
    public function getItemTransactions(): array
    {
        $out = [];
        foreach ((array) ($this->data['itemTransactions'] ?? []) as $it) {
            if (!empty($it['paymentTransactionId'])) {
                $out[(string) $it['paymentTransactionId']] = number_format((float) ($it['paidPrice'] ?? $it['price'] ?? 0), 2, '.', '');
            }
        }

        return $out;
    }
}
