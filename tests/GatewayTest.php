<?php namespace Omnipay\Iyzico;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\GatewayTestCase;

/**
 * Iyzico Gateway Test
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'apiId' => '6700000067',
            'secretKey' => '67000067',
            'mode' => 'test',
            'amount' => 100,
            'currency' => 'TRY',
            'card' => new CreditCard(array(
                'number'        => '4506341010205499',
                'expiryMonth'   => '03',
                'expiryYear'    => '2017',
                'cvv'           => '000'
            )),
        );
    }

    public function testPurchase()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\Iyzico\Message\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('130215141054377801316798', $response->getTransactionReference());
    }

    public function testPurchaseError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Input variable errors', $response->getMessage());
    }
}
