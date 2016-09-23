<?php
use Flutterwave\FlutterEncrypt;

class FlutterEncryptTest extends \PHPUnit_Framework_TestCase {

  public function testEncryptWorks() {
    $result = FlutterEncrypt::encrypt3Des("Ridwan", "tk_NabYp2XjZ6G9WwdFruzK");
    $this->assertNotEmpty($result);
    return $result;
  }

  /**
   * @depends testEncryptWorks
   */
  public function testDecryptWorks($result) {
    $rawData = "Ridwan";
    $res = FlutterEncrypt::decrypt3Des($result, "tk_NabYp2XjZ6G9WwdFruzK");
    $this->assertEquals($rawData, $res);
  }
}
