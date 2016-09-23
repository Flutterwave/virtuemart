<?php
use Flutterwave\Ip;

class IpTest extends PHPUnit_Framework_TestCase {
  private $response;

  public function setUp() {
    $this->response = Ip::check("197.149.91.202");
  }

  public function testIpResponseSuccessful() {
    $this->assertTrue($this->response->isSuccessfulResponse());
  }

  public function testIpStatusCodeIsOk() {
    $this->assertEquals(200, $this->response->getStatusCode());
  }

  public function testResponseDataNotEmpty() {
    $this->assertNotEmpty($this->response->getResponseData());
  }
}
