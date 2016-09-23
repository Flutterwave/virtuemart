<?php
use Flutterwave\ApiRequest;
use Flutterwave\Logging;

class ApiRequestTest extends \PHPUnit_Framework_TestCase {

  function __construct()
  {
    $this->log = Logging::getLoggerInstance();
  }

  public function testMakePostRequestWorks() {
    $url = "http://staging1flutterwave.co:8080/pwc/rest/fw/ipcheck/";
    $resp = (new ApiRequest($url))
                ->addBody("ip", "197.149.91.202")
                ->makePostRequest();
    $this->assertEquals(200, $resp->getStatusCode());
  }
}
