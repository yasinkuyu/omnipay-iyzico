<?php

namespace Omnipay\Iyzico\Message;

/**
 * Ödeme formunu başlatır (checkoutform/initialize/auth/ecom).
 *
 * Parametreler: amount, currency, transactionId (conversationId ve basketId), returnUrl (callbackUrl), clientIp,
 * card (ad, soyad, e-posta, telefon, fatura / teslimat adresi, şehir, ülke), items (sepet; yoksa tek satır),
 * enabledInstallments ([1, 2, 3, 6, 9] gibi), identityNumber (alıcı TCKN; yoksa 11111111111), buyerId, locale (tr | en).
 * Pazaryeri hesabı: subMerchantKey (tüm satırlar o alt üye iş yerine; satır tutarı kadar subMerchantPrice) ya da
 * satır başına items[].subMerchantKey / subMerchantPrice (ItemBag yerine dizi verildiğinde).
 */
class PurchaseRequest extends AbstractRequest
{
    public function getEnabledInstallments() { return $this->getParameter('enabledInstallments'); }
    public function setEnabledInstallments($value) { return $this->setParameter('enabledInstallments', $value); }

    public function getIdentityNumber() { return $this->getParameter('identityNumber'); }
    public function setIdentityNumber($value) { return $this->setParameter('identityNumber', $value); }

    public function getBuyerId() { return $this->getParameter('buyerId'); }
    public function setBuyerId($value) { return $this->setParameter('buyerId', $value); }

    public function getSubMerchantKey() { return $this->getParameter('subMerchantKey'); }
    public function setSubMerchantKey($value) { return $this->setParameter('subMerchantKey', $value); }

    public function getData(): array
    {
        $this->validate('amount', 'transactionId', 'returnUrl', 'card');
        $c = $this->getCard();
        $first = trim((string) $c->getBillingFirstName()) ?: 'Musteri';
        $last = trim((string) $c->getBillingLastName()) ?: $first;
        $addr = fn ($a1, $a2) => mb_substr(trim($a1 . ' ' . $a2) ?: 'Adres belirtilmedi', 0, 250);
        $city = trim((string) $c->getBillingCity()) ?: 'Istanbul';
        $country = $this->country((string) $c->getBillingCountry());
        $sCity = trim((string) $c->getShippingCity()) ?: $city;
        $sCountry = $c->getShippingCountry() ? $this->country((string) $c->getShippingCountry()) : $country;
        $tc = preg_replace('/\D/', '', (string) $this->getIdentityNumber());
        $inst = array_values(array_unique(array_filter(array_map('intval', (array) ($this->getEnabledInstallments() ?: [1])), fn ($n) => $n >= 1 && $n <= 12)));
        $total = self::price($this->getAmount());

        return [
            'locale' => (string) ($this->getLocale() ?: 'tr'),
            'conversationId' => (string) $this->getTransactionId(),
            'price' => $total,
            'paidPrice' => $total,
            'currency' => $this->currencyCode(),
            'basketId' => (string) $this->getTransactionId(),
            'paymentGroup' => 'PRODUCT',
            'callbackUrl' => (string) $this->getReturnUrl(),
            'enabledInstallments' => $inst ?: [1],
            'buyer' => [
                'id' => (string) ($this->getBuyerId() ?: 'B' . $this->getTransactionId()),
                'name' => $first,
                'surname' => $last,
                'gsmNumber' => (string) $c->getBillingPhone(),
                'email' => (string) $c->getEmail(),
                'identityNumber' => strlen($tc) === 11 ? $tc : '11111111111',
                'registrationAddress' => $addr($c->getBillingAddress1(), $c->getBillingAddress2()),
                'ip' => (string) ($this->getClientIp() ?: '127.0.0.1'),
                'city' => $city,
                'country' => $country,
            ],
            'shippingAddress' => [
                'contactName' => trim(($c->getShippingFirstName() ?: $first) . ' ' . ($c->getShippingLastName() ?: $last)),
                'city' => $sCity,
                'country' => $sCountry,
                'address' => $addr($c->getShippingAddress1() ?: $c->getBillingAddress1(), $c->getShippingAddress2() ?: $c->getBillingAddress2()),
            ],
            'billingAddress' => [
                'contactName' => trim($first . ' ' . $last),
                'city' => $city,
                'country' => $country,
                'address' => $addr($c->getBillingAddress1(), $c->getBillingAddress2()),
            ],
            'basketItems' => $this->basketItems(),
        ];
    }

    private function country(string $c): string
    {
        return $c === '' || in_array(mb_strtolower($c), ['türkiye', 'turkiye', 'tr', 'turkey'], true) ? 'Turkey' : $c;
    }

    /** Satırlar (satır tutarı = birim × adet). Toplam tutmazsa tek satır. */
    private function basketItems(): array
    {
        $sub = (string) ($this->getSubMerchantKey() ?? '');
        $lines = [];
        $sum = 0.0;
        $i = 0;
        foreach ($this->getItems() ?? [] as $item) {
            $line = round((float) $item->getPrice() * max(1, (int) $item->getQuantity()), 2);
            if ($line <= 0) {
                continue;
            }
            $sum += $line;
            $lines[] = ['id' => 'L' . (++$i), 'name' => mb_substr((string) $item->getName(), 0, 120) ?: 'Urun', 'category1' => 'Urun',
                'itemType' => 'PHYSICAL', 'price' => self::price($line)];
        }
        if (!$lines || abs($sum - (float) $this->getAmount()) >= 0.01) {
            $lines = [['id' => 'ORDER', 'name' => (string) ($this->getDescription() ?: 'Siparis ' . $this->getTransactionId()),
                'category1' => 'Urun', 'itemType' => 'PHYSICAL', 'price' => self::price($this->getAmount())]];
        }
        if ($sub !== '') {
            foreach ($lines as &$l) {
                $l['subMerchantKey'] = $sub;
                $l['subMerchantPrice'] = $l['price'];
            }
            unset($l);
        }

        return $lines;
    }

    public function sendData($data): PurchaseResponse
    {
        return $this->response = new PurchaseResponse($this, $this->call('/payment/iyzipos/checkoutform/initialize/auth/ecom', $data));
    }
}
