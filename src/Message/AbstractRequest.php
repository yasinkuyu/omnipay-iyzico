<?php

namespace Omnipay\Iyzico\Message;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

/**
 * IYZWSv2 imzalı JSON istekleri. Authorization = "IYZWSv2 " + base64("apiKey:… &randomKey:… &signature:…"),
 * signature = HMAC-SHA256(randomKey + uri yolu + gövde, secretKey) (hex).
 */
abstract class AbstractRequest extends BaseAbstractRequest
{
    public const LIVE = 'https://api.iyzipay.com';
    public const SANDBOX = 'https://sandbox-api.iyzipay.com';

    public function getApiKey() { return $this->getParameter('apiKey'); }
    public function setApiKey($value) { return $this->setParameter('apiKey', $value); }

    public function getSecretKey() { return $this->getParameter('secretKey'); }
    public function setSecretKey($value) { return $this->setParameter('secretKey', $value); }

    public function getLocale() { return $this->getParameter('locale'); }
    public function setLocale($value) { return $this->setParameter('locale', $value); }

    /** iyzico tutar biçimi: "100.0", "12.5", "9.99" */
    public static function price($v): string
    {
        $s = rtrim(number_format((float) $v, 2, '.', ''), '0');

        return str_ends_with($s, '.') ? $s . '0' : $s;
    }

    protected function currencyCode(): string
    {
        $c = strtoupper((string) ($this->getCurrency() ?: 'TRY'));

        return in_array($c, ['TRY', 'USD', 'EUR', 'GBP', 'IRR', 'NOK', 'RUB', 'CHF'], true) ? $c : 'TRY';
    }

    /**
     * İmzalı POST. Okunamayan yanıtta null (işlem gerçekleşmiş olabilir); bağlantı hatasında Omnipay'in NetworkException'ı fırlar.
     */
    protected function call(string $path, array $body): ?array
    {
        $this->validate('apiKey', 'secretKey');
        $json = (string) json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $rnd = (string) (int) (microtime(true) * 1000) . random_int(100000, 999999);
        $sig = hash_hmac('sha256', $rnd . $path . $json, (string) $this->getSecretKey());
        $auth = base64_encode('apiKey:' . $this->getApiKey() . '&randomKey:' . $rnd . '&signature:' . $sig);
        $res = $this->httpClient->request('POST', ($this->getTestMode() ? self::SANDBOX : self::LIVE) . $path, [
            'Authorization' => 'IYZWSv2 ' . $auth,
            'x-iyzi-rnd' => $rnd,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $json);
        $out = json_decode((string) $res->getBody(), true);

        return is_array($out) ? $out : null;
    }
}
