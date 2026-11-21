# Omnipay: iyzico

**iyzico için [Omnipay](https://github.com/thephpleague/omnipay) sürücüsü**

[![Latest Stable Version](https://poser.pugx.org/yasinkuyu/omnipay-iyzico/v/stable)](https://packagist.org/packages/yasinkuyu/omnipay-iyzico)
[![Total Downloads](https://poser.pugx.org/yasinkuyu/omnipay-iyzico/downloads)](https://packagist.org/packages/yasinkuyu/omnipay-iyzico)
[![License](https://poser.pugx.org/yasinkuyu/omnipay-iyzico/license)](https://packagist.org/packages/yasinkuyu/omnipay-iyzico)

**Türkçe** | [English](README_EN.md)

## Bu paket ne yapar?

Mağazanızda iyzico ile kartla ödeme almanızı sağlar. Müşteri **iyzico'nun Ödeme Formu'na** (ödeme sayfası) gider,
kartını ve taksitini orada seçer, 3D Secure doğrulamasını yapar ve sitenize döner. Kart bilgisi sizin sunucunuzdan geçmez.
Ödemenin sonucu, dönüşte gelen jetonla **iyzico'ya sunucudan sorularak** kesinleşir.

```
Sunucunuz ──(formu başlat)──▶ iyzico ──▶ ödeme sayfası adresi + jeton
Müşteri ──▶ iyzico ödeme sayfası (kart + taksit + 3D Secure) ──▶ Siteniz (dönüş, jetonla)
Sunucunuz ──(jetonla sorgu)──▶ iyzico ──▶ ödeme kesinleşir
```

iyzico'nun PHP SDK'sına bağımlı değildir: istekler doğrudan IYZWSv2 ile imzalanır.

**Özellikler**

| | |
|---|---|
| Ödeme | iyzico Ödeme Formu (Checkout Form) |
| Taksit | Sunulacak taksitleri siz belirlersiniz (`enabledInstallments`), vade farkını iyzico ekler |
| İptal / iade | `void()` (aynı gün, tamamı), `refund()` kalem bazında, tam ya da kısmi |
| Sorgu | `fetchTransaction()` |
| Pazaryeri | Alt üye iş yeri (`subMerchantKey`) |

## Kurulum

```bash
composer require yasinkuyu/omnipay-iyzico php-http/guzzle7-adapter
```

PHP 8.1 ya da üstü gerekir.

## iyzico'dan almanız gereken bilgiler

iyzico panelinde **Ayarlar → Firma Ayarları**: `apiKey` (API anahtarı) ve `secretKey` (güvenlik anahtarı).
Deneme için sandbox-merchant.iyzipay.com'dan ayrı bir sandbox hesabı açın.

## Hızlı başlangıç

### 1. Gateway'i hazırlayın

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');
$gateway->initialize([
    'apiKey'    => 'sandbox-…',
    'secretKey' => 'sandbox-…',
    'testMode'  => true,   // canlıya geçerken false
]);
```

### 2. Ödemeyi başlatın (odeme.php)

```php
$response = $gateway->purchase([
    'amount'              => '149.90',
    'currency'            => 'TRY',
    'transactionId'       => 'SIP-10045',                       // sizin sipariş numaranız (basketId)
    'returnUrl'           => 'https://magaza.com/odeme-donus.php',
    'clientIp'            => $_SERVER['REMOTE_ADDR'],
    'enabledInstallments' => [1, 2, 3, 6],
    'identityNumber'      => '11111111111',                    // alıcı TCKN (yoksa bu değer gider)
    'card'  => ['email' => 'ali@ornek.com', 'billingFirstName' => 'Ali', 'billingLastName' => 'Kaya',
                'billingPhone' => '+905551112233', 'billingAddress1' => 'Adres', 'billingCity' => 'Istanbul'],
    'items' => [['name' => 'Kazak', 'quantity' => 2, 'price' => '50.00'], ['name' => 'Kargo', 'quantity' => 1, 'price' => '49.90']],
])->send();

if ($response->isRedirect()) {
    // $response->getToken() saklanabilir
    $response->redirect();
} else {
    echo $response->getMessage();
}
```

### 3. Dönüşü doğrulayın (odeme-donus.php)

iyzico tarayıcıyı `returnUrl`'e yalnız `token` ile POST eder; paket sonucu iyzico'dan sorgular:

```php
$result = $gateway->completePurchase([
    'transactionId' => 'SIP-10045', // KENDİ kaydınızdaki sipariş no
    'amount'        => '149.90',    // KENDİ kaydınızdaki tutar
])->send();

if ($result->isSuccessful()) {
    $paymentId = $result->getTransactionReference();   // iptal ve sorgu için saklayın
    $items     = $result->getItemTransactions();       // [paymentTransactionId => tutar], iade için saklayın
    $result->getPaidAmount();                          // vade farkı dahil ödenen
    $result->getInstallment();
} elseif ($result->isPendingReview()) {
    // iyzico dolandırıcılık incelemesinde: sonuç sonra kesinleşir
} else {
    echo $result->getMessage();
}
```

## İptal, iade, sorgu

```php
$gateway->void(['transactionReference' => $paymentId, 'clientIp' => $ip])->send();                 // aynı gün, tamamı
$gateway->refund(['transactionReference' => $paymentTransactionId, 'amount' => '30.00', 'clientIp' => $ip])->send(); // kalem iadesi
$gateway->fetchTransaction(['transactionReference' => $paymentId])->send()->getData();
```

İade kalem (paymentTransactionId) bazındadır ve bir kalemin ödenen tutarını aşamaz; çok kalemli sepette tutarı
kalemlere bölün. **`isReadable()` false dönerse** yanıt okunamamıştır: işlem gerçekleşmiş olabilir. Otomatik olarak
yeniden denemeyin (iyzico istekleri yinelemeye dayanıklı değildir); iyzico panelinden kontrol edin.

## Pazaryeri (alt üye iş yeri)

Hesabınız pazaryeriyse sepet satırları bir alt üye iş yerine bağlanmalıdır: `'subMerchantKey' => '…'` verin; her satıra
satır tutarı kadar `subMerchantPrice` eklenir.

## Test

iyzico sandbox'ında (`testMode => true`) test kartı: `5528 7900 0000 0008`, son kullanma 12/30, CVV `123`.
Bu sürüm iyzico sandbox'ında uçtan uca denendi: ödeme formu, onaylı ödeme ve sunucudan sorgu, kalem iadesi, iptal.

## Sık karşılaşılan durumlar

| Hata | Neden |
|---|---|
| `5077` subMerchantKey gönderilmelidir | Hesap pazaryeri hesabı: `subMerchantKey` verin |
| `1000` / imza hatası | `apiKey` / `secretKey` yanlış ya da sandbox ile canlı karıştı |
| `5093` iade tutarı büyük olamaz | İade kalemin ödenen tutarını aşıyor |
| `isVerified()` false | Sorgu başarısız ya da sipariş no / tutar sizin kaydınızla uyuşmuyor |

## 3.x'ten geçiş

4.0 iyzico SDK'sını kaldırdı ve baştan yazıldı: `purchase()` Ödeme Formu'nu başlatır (eski doğrudan kartla satış
yok), sonuç `completePurchase()` ile sorgulanır, iade kalem bazındadır. Eski sürümü (3.x) kullanmaya devam etmek
isterseniz: `composer require yasinkuyu/omnipay-iyzico:^3.0`

## Testler

```bash
composer install
vendor/bin/phpunit
```

## Diğer Omnipay paketleri

- **[Omnipay: NestPay (EST / Payten)](https://github.com/yasinkuyu/omnipay-nestpay)** — İş Bankası, Akbank, Halkbank, Ziraat, TEB, QNB, ING, Şekerbank, Anadolubank sanal POS'u; 3D Pay Hosting
- **[Omnipay: GVP (Garanti BBVA)](https://github.com/yasinkuyu/omnipay-gvp)** — Garanti BBVA sanal POS'u; Ortak Ödeme Sayfası
- **[Omnipay: Akbank](https://github.com/yasinkuyu/omnipay-akbank)** — Akbank sanal POS'u (yeni altyapı); ortak ödeme sayfası
- **[Omnipay: QNB](https://github.com/yasinkuyu/omnipay-qnb)** — QNB sanal POS'u (PayFor); ortak ödeme sayfası
- **[Omnipay: Paratika](https://github.com/yasinkuyu/omnipay-paratika)** — Paratika, VakıfPayS ve ZiraatPay ortak ödeme sayfası
- **[Omnipay: PayTR](https://github.com/yasinkuyu/omnipay-paytr)** — PayTR iFrame ödeme sayfası
- **[Omnipay: Paycell SDK](https://github.com/yasinkuyu/omnipay-paycell-sdk)** — Turkcell Paycell ödeme sayfası (Web SDK)
- **[Omnipay: Paycell API](https://github.com/yasinkuyu/omnipay-paycell)** — Turkcell Paycell ödeme API'si
- **[Omnipay: Posnet](https://github.com/yasinkuyu/omnipay-posnet)** — Yapı Kredi Posnet sanal POS'u

## Lisans

MIT — [Yasin Kuyu](https://github.com/yasinkuyu)
