<?php
use Flutterwave\Bvn;
use Flutterwave\Flutterwave;

class BvnTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    Flutterwave::setMerchantCredentials("tk_tdyrSMQo8a", "tk_NabYp2XjZ6G9WwdFruzK");
  }

  public function testBvnVerify() {
    $resp = Bvn::verify("22254824829", Flutterwave::SMS);
    $data = $resp->getResponseData();
    $this->assertTrue($resp->isSuccessfulResponse());
    $this->assertEquals(200, $resp->getStatusCode());
    $this->assertNotEmpty($resp->getResponseData());

    return $data['data']['transactionReference'];
  }

  public function testBvnValidate() {
    $resp = Bvn::validate("22254824829", "78215", "FLW00291016");
    $this->assertTrue($resp->isSuccessfulResponse());
    $this->assertEquals(200, $resp->getStatusCode());
  }

  /**
  * @depends testBvnVerify
  */
  public function testResendOtp($ref) {
    $resp = Bvn::resendOtp($ref, Flutterwave::SMS);
    $this->assertTrue($resp->isSuccessfulResponse());
    $this->assertEquals(200, $resp->getStatusCode());
    $this->assertNotEmpty($resp->getResponseData());
  }
}
