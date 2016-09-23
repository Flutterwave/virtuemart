<?php
use Flutterwave\Banks;
use GuzzleHttp\Client;

class BanksTest extends PHPUnit_Framework_TestCase {
  public function testBanksList() {
    $resp = Banks::allBanks();
    $this->assertTrue($resp->isSuccessfulResponse());
  }
}
