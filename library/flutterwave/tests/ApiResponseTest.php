<?php
use Flutterwave\ApiRequest;
use Flutterwave\ApiResponse;

class ApiResponseTest extends PHPUnit_Framework_TestCase {
  public function testApiResponseIsOk() {
    $url = "http://staging1flutterwave.co:8080/pwc/rest/fw/banks/";
    $resp = (new ApiRequest($url))
                ->makePostRequest();
    $this->assertEquals(200, $resp->getStatusCode());
    $this->assertNotEmpty($resp->getResponseData());
    $this->assertTrue($resp->isSuccessfulResponse());
  }
}
