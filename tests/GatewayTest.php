<?php

namespace Omnipay\Iyzico\Tests;

use Omnipay\Iyzico\Gateway;
use Omnipay\Iyzico\Message\AbstractRequest;
use Omnipay\Tests\GatewayTestCase;
use Symfony\Component\HttpFoundation\Request;

class GatewayTest extends GatewayTestCase
{
    /** @var Gateway */
    protected $gateway;

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->initialize(['apiKey' => 'sandbox-key', 'secretKey' => 'sandbox-secret', 'testMode' => true]);
    }

    private function last(): array
    {
        $req = $this->getMockClient()->getLastRequest();

        return [json_decode((string) $req->getBody(), true), $req];
    }

    private function purchase(array $o = [])
    {
        return $this->gateway->purchase($o + [
            'amount' => '149.90', 'transactionId' => 'SIP1', 'returnUrl' => 'https://shop.test/r', 'clientIp' => '1.2.3.4', 'enabledInstallments' => [1, 3, 6],
            'card' => ['email' => 'a@b.test', 'billingFirstName' => 'Ada', 'billingLastName' => 'Kaya', 'billingPhone' => '+905551112233', 'billingAddress1' => 'Adres', 'billingCity' => 'Istanbul'],
            'items' => [['name' => 'Kazak', 'quantity' => 2, 'price' => '50.00'], ['name' => 'Kargo', 'quantity' => 1, 'price' => '49.90']],
        ])->send();
    }

    public function testPurchaseSignsRequestAndRedirects(): void
    {
        $this->setMockHttpResponse('InitSuccess.txt');
        $r = $this->purchase();
        $this->assertTrue($r->isRedirect());
        $this->assertSame('GET', $r->getRedirectMethod());
        $this->assertStringStartsWith('https://sandbox-cpp.iyzipay.com/?token=', $r->getRedirectUrl());
        $this->assertSame('c96a5d2c-7a13-497f-a704-d0b90eec64c0', $r->getToken());

        [$b, $req] = $this->last();
        $this->assertSame(AbstractRequest::SANDBOX . '/payment/iyzipos/checkoutform/initialize/auth/ecom', (string) $req->getUri());
        $this->assertSame(['149.9', '149.9', 'TRY', 'SIP1', [1, 3, 6]], [$b['price'], $b['paidPrice'], $b['currency'], $b['basketId'], $b['enabledInstallments']]);
        $this->assertSame([['L1', '100.0'], ['L2', '49.9']], array_map(fn ($i) => [$i['id'], $i['price']], $b['basketItems']));
        $this->assertSame('11111111111', $b['buyer']['identityNumber']);

        // IYZWSv2: base64("apiKey:…&randomKey:…&signature:" + HMAC-SHA256(rnd + yol + gövde))
        $rnd = $req->getHeaderLine('x-iyzi-rnd');
        $expect = base64_encode('apiKey:sandbox-key&randomKey:' . $rnd . '&signature:' . hash_hmac('sha256', $rnd . '/payment/iyzipos/checkoutform/initialize/auth/ecom' . (string) $req->getBody(), 'sandbox-secret'));
        $this->assertSame('IYZWSv2 ' . $expect, $req->getHeaderLine('Authorization'));
    }

    public function testMarketplaceSubMerchant(): void
    {
        $this->setMockHttpResponse('InitSuccess.txt');
        $this->purchase(['subMerchantKey' => 'SUBKEY']);
        [$b] = $this->last();
        $this->assertSame(['SUBKEY', '100.0'], [$b['basketItems'][0]['subMerchantKey'], $b['basketItems'][0]['subMerchantPrice']]);
    }

    public function testPurchaseFailure(): void
    {
        $this->setMockHttpResponse('InitFailure.txt');
        $r = $this->purchase();
        $this->assertFalse($r->isRedirect());
        $this->assertSame('5077', $r->getCode());
    }

    public function testCompletePurchaseReadsTokenFromCallbackAndQueriesServer(): void
    {
        $this->setMockHttpResponse('DetailSuccess.txt');
        $this->gateway = new Gateway($this->getHttpClient(), Request::create('/r', 'POST', ['token' => 'c96a5d2c']));
        $this->gateway->initialize(['apiKey' => 'sandbox-key', 'secretKey' => 'sandbox-secret', 'testMode' => true]);
        $r = $this->gateway->completePurchase(['transactionId' => 'SIP1', 'amount' => '149.90'])->send();

        $this->assertTrue($r->isVerified());
        $this->assertTrue($r->isSuccessful());
        $this->assertSame(['39048348', '552879******0008', 1, 'completed'], [$r->getTransactionReference(), $r->getMaskedCard(), $r->getInstallment(), $r->getTransactionStatus()]);
        $this->assertSame(['41021144' => '100.00', '41021145' => '49.90'], $r->getItemTransactions());
        [$b, $req] = $this->last();
        $this->assertSame('c96a5d2c', $b['token']);
        $this->assertStringEndsWith('/payment/iyzipos/checkoutform/auth/ecom/detail', (string) $req->getUri());
    }

    public function testOtherOrderOrLowerAmountIsNotVerified(): void
    {
        $this->setMockHttpResponse('DetailSuccess.txt');
        $this->assertFalse($this->gateway->completePurchase(['token' => 't', 'transactionId' => 'BASKA'])->send()->isVerified());
        $this->setMockHttpResponse('DetailSuccess.txt');
        $this->assertFalse($this->gateway->completePurchase(['token' => 't', 'amount' => '200.00'])->send()->isSuccessful());
    }

    public function testFailedAndReviewPayments(): void
    {
        $this->setMockHttpResponse('DetailFailure.txt');
        $r = $this->gateway->completePurchase(['token' => 't', 'transactionId' => 'SIP1'])->send();
        $this->assertTrue($r->isVerified());
        $this->assertFalse($r->isSuccessful());
        $this->assertSame(['failed', 'Kart limiti yetersiz'], [$r->getTransactionStatus(), $r->getMessage()]);

        $this->setMockHttpResponse('DetailReview.txt');
        $r = $this->gateway->completePurchase(['token' => 't'])->send();
        $this->assertTrue($r->isPendingReview());
        $this->assertFalse($r->isSuccessful());
        $this->assertSame('pending', $r->getTransactionStatus());
    }

    public function testRefundVoidAndDetail(): void
    {
        $this->setMockHttpResponse('RefundSuccess.txt');
        $this->assertTrue($this->gateway->refund(['transactionReference' => '41021144', 'amount' => '30.00', 'clientIp' => '1.2.3.4'])->send()->isSuccessful());
        [$b, $req] = $this->last();
        $this->assertSame(['41021144', '30.0', 'TRY'], [$b['paymentTransactionId'], $b['price'], $b['currency']]);
        $this->assertStringEndsWith('/payment/refund', (string) $req->getUri());

        $this->setMockHttpResponse('RefundTooMuch.txt');
        $r = $this->gateway->refund(['transactionReference' => '41021145', 'amount' => '100.00'])->send();
        $this->assertSame('5093', $r->getCode());

        $this->setMockHttpResponse('RefundSuccess.txt');
        $this->gateway->void(['transactionReference' => '39048348'])->send();
        [$b, $req] = $this->last();
        $this->assertSame('39048348', $b['paymentId']);
        $this->assertStringEndsWith('/payment/cancel', (string) $req->getUri());

        $this->setMockHttpResponse('DetailSuccess.txt');
        $this->assertCount(2, $this->gateway->fetchTransaction(['transactionReference' => '39048348'])->send()->getItemTransactions());

        $this->setMockHttpResponse('Unreadable.txt');
        $this->assertFalse($this->gateway->void(['transactionReference' => '1'])->send()->isReadable());
    }
}
