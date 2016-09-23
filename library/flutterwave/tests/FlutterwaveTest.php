<?php
use Flutterwave\Flutterwave;

class FlutterwaveTest extends \PHPUnit_Framework_TestCase {

  public function testConstructorMissingMerchantKey() {
    // $this->expectedException(InvalidArgumentException::class);
    try {
      $c = Flutterwave::setMerchantCredentials("", "836363");
    } catch (Exception $e) {
      $this->assertEquals("Merchant key can not be empty", $e->getMessage(), "thrown exception for missing argument is correct");
      return;
    }
    $this->fail("Expected Exception has not been raised.");
  }

  public function testConstructorMissingApiKey() {
    // $this->expectedException(InvalidArgumentException::class);
    try {
      $c = Flutterwave::setMerchantCredentials("63535353", "");
    } catch (Exception $e) {
      $this->assertEquals("Api key can not be empty", $e->getMessage(), "thrown exception for missing argument is correct");
      return;
    }
    $this->fail("Expected Exception has not been raised.");
  }

  public function testGetMerchantKey() {
    $c = Flutterwave::setMerchantCredentials("1111", "2222");
    $this->assertEquals("1111", Flutterwave::getMerchantKey());
  }

  public function testGetApiKey() {
    $c = Flutterwave::setMerchantCredentials("1111", "2222");
    $this->assertEquals("2222", Flutterwave::getApiKey());
  }

  public function testInvalidEnvVariableValue() {
    try {
      $c = Flutterwave::setMerchantCredentials("63535353", "76464", "blabla");
    } catch (Exception $e) {
      $this->assertEquals("env variable can only be `staging` or `production`", $e->getMessage(), "thrown exception for missing argument is correct");
      return;
    }
    $this->fail("Expected Exception has not been raised.");
  }

}
