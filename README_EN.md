# Omnipay: iyzico

**[Omnipay](https://github.com/thephpleague/omnipay) driver for iyzico**

[![Latest Stable Version](https://poser.pugx.org/yasinkuyu/omnipay-iyzico/v/stable)](https://packagist.org/packages/yasinkuyu/omnipay-iyzico)
[![Total Downloads](https://poser.pugx.org/yasinkuyu/omnipay-iyzico/downloads)](https://packagist.org/packages/yasinkuyu/omnipay-iyzico)
[![License](https://poser.pugx.org/yasinkuyu/omnipay-iyzico/license)](https://packagist.org/packages/yasinkuyu/omnipay-iyzico)

[Türkçe](README.md) | **English**

## What does this package do?

It lets your shop take card payments with iyzico. The customer goes to **iyzico's Checkout Form** (hosted payment
page), enters the card and picks installments there, completes 3D Secure and comes back to your site. Card data never
touches your server. The result is confirmed by **querying iyzico server-side** with the token from the return.

```
Your server ──(initialize form)──▶ iyzico ──▶ payment page URL + token
Customer ──▶ iyzico payment page (card + installments + 3D Secure) ──▶ Your site (return, with token)
Your server ──(query with token)──▶ iyzico ──▶ payment confirmed
```

No dependency on iyzico's PHP SDK: requests are signed directly with IYZWSv2.

**Features**

| | |
|---|---|
| Payment | iyzico Checkout Form |
| Installments | You choose which are offered (`enabledInstallments`); iyzico adds the interest |
| Cancel / refund | `void()` (same day, full), `refund()` per item, full or partial |
| Status | `fetchTransaction()` |
| Marketplace | Sub-merchants (`subMerchantKey`) |

## Installation

```bash
composer require yasinkuyu/omnipay-iyzico php-http/guzzle7-adapter
```

Requires PHP 8.1 or later.

## What you need from iyzico

iyzico panel → **Settings → Company settings**: `apiKey` and `secretKey`. For testing open a separate sandbox account
at sandbox-merchant.iyzipay.com.

## Quick start

### 1. Set up the gateway

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Iyzico');
$gateway->initialize([
    'apiKey'    => 'sandbox-…',
    'secretKey' => 'sandbox-…',
    'testMode'  => true,   // false in production
]);
```

### 2. Start the payment (pay.php)

```php
$response = $gateway->purchase([
    'amount'              => '149.90',
    'currency'            => 'TRY',
    'transactionId'       => 'ORD-10045',                     // your order number (basketId)
    'returnUrl'           => 'https://shop.com/pay-return.php',
    'clientIp'            => $_SERVER['REMOTE_ADDR'],
    'enabledInstallments' => [1, 2, 3, 6],
    'identityNumber'      => '11111111111',                  // buyer's national ID (this value if unknown)
    'card'  => ['email' => 'ali@example.com', 'billingFirstName' => 'Ali', 'billingLastName' => 'Kaya',
                'billingPhone' => '+905551112233', 'billingAddress1' => 'Address', 'billingCity' => 'Istanbul'],
    'items' => [['name' => 'Sweater', 'quantity' => 2, 'price' => '50.00'], ['name' => 'Shipping', 'quantity' => 1, 'price' => '49.90']],
])->send();

if ($response->isRedirect()) {
    // $response->getToken() can be stored
    $response->redirect();
} else {
    echo $response->getMessage();
}
```

### 3. Verify the return (pay-return.php)

iyzico POSTs the browser back to `returnUrl` with only a `token`; the package queries iyzico for the result:

```php
$result = $gateway->completePurchase([
    'transactionId' => 'ORD-10045', // the order number from YOUR records
    'amount'        => '149.90',    // the amount from YOUR records
])->send();

if ($result->isSuccessful()) {
    $paymentId = $result->getTransactionReference();   // keep it for cancel and status
    $items     = $result->getItemTransactions();       // [paymentTransactionId => amount], keep it for refunds
    $result->getPaidAmount();                          // paid, incl. installment interest
    $result->getInstallment();
} elseif ($result->isPendingReview()) {
    // under iyzico fraud review: the result is settled later
} else {
    echo $result->getMessage();
}
```

## Cancel, refund, status

```php
$gateway->void(['transactionReference' => $paymentId, 'clientIp' => $ip])->send();                 // same day, full
$gateway->refund(['transactionReference' => $paymentTransactionId, 'amount' => '30.00', 'clientIp' => $ip])->send(); // per item
$gateway->fetchTransaction(['transactionReference' => $paymentId])->send()->getData();
```

Refunds are per item (paymentTransactionId) and cannot exceed what was paid for that item; split the amount across
items for multi-item baskets. **If `isReadable()` returns false**, the response could not be read: the operation may
have gone through. Do not retry automatically (iyzico requests are not idempotent); check the iyzico panel.

## Marketplace (sub-merchants)

If your account is a marketplace, basket items must be tied to a sub-merchant: pass `'subMerchantKey' => '…'`; each
item gets `subMerchantPrice` equal to its price.

## Testing

In the iyzico sandbox (`testMode => true`) the test card is `5528 7900 0000 0008`, expiry 12/30, CVV `123`.
This version was tested end to end in the iyzico sandbox: checkout form, approved payment and server-side query,
item refund, cancel.

## Troubleshooting

| Error | Cause |
|---|---|
| `5077` subMerchantKey required | Marketplace account: pass `subMerchantKey` |
| `1000` / signature error | Wrong `apiKey` / `secretKey`, or sandbox and live mixed up |
| `5093` refund amount too large | The refund exceeds what was paid for that item |
| `isVerified()` false | The query failed, or the order number / amount don't match your record |

## Upgrading from 3.x

4.0 drops the iyzico SDK and is a rewrite: `purchase()` initializes the Checkout Form (no direct card sale), the result
is queried with `completePurchase()`, refunds are per item. To keep using the old version (3.x):
`composer require yasinkuyu/omnipay-iyzico:^3.0`

## Tests

```bash
composer install
vendor/bin/phpunit
```

## Other Omnipay packages

- **[Omnipay: NestPay (EST / Payten)](https://github.com/yasinkuyu/omnipay-nestpay)** — İş Bankası, Akbank, Halkbank, Ziraat, TEB, QNB, ING, Şekerbank, Anadolubank virtual POS; 3D Pay Hosting
- **[Omnipay: GVP (Garanti BBVA)](https://github.com/yasinkuyu/omnipay-gvp)** — Garanti BBVA virtual POS; hosted payment page
- **[Omnipay: Akbank](https://github.com/yasinkuyu/omnipay-akbank)** — Akbank virtual POS (new platform); hosted payment page
- **[Omnipay: QNB](https://github.com/yasinkuyu/omnipay-qnb)** — QNB virtual POS (PayFor); hosted payment page
- **[Omnipay: Paratika](https://github.com/yasinkuyu/omnipay-paratika)** — Paratika, VakıfPayS and ZiraatPay hosted payment page
- **[Omnipay: PayTR](https://github.com/yasinkuyu/omnipay-paytr)** — PayTR iFrame payment page
- **[Omnipay: Paycell SDK](https://github.com/yasinkuyu/omnipay-paycell-sdk)** — Turkcell Paycell payment page (Web SDK)
- **[Omnipay: Paycell API](https://github.com/yasinkuyu/omnipay-paycell)** — Turkcell Paycell payment API
- **[Omnipay: Posnet](https://github.com/yasinkuyu/omnipay-posnet)** — Yapı Kredi Posnet virtual POS

## License

MIT — [Yasin Kuyu](https://github.com/yasinkuyu)
