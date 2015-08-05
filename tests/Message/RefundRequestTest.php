<?php namespace Omnipay\Iyzico\Message;

use Omnipay\Tests\TestCase;

/**
 * Iyzico Gateway Refund RequestTest
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-iyzico
 */
class RefundRequestTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '11.00',
                'currency' => 'TRY',
                'testMode' => true,
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        /*
         * See https://bugs.php.net/bug.php?id=29500 for why this is cast to string
         */
        $this->assertSame('CC.DB', (string)$data['Type']);
        $this->assertSame('1200', (string)$data['Amount']);
        $this->assertSame('TRY', (string)$data['Currency']);
    }

}
