<?php

use Flutterwave\Flutterwave;
use Flutterwave\Transaction;
use Flutterwave\Card;
use Flutterwave\Currencies;
use Flutterwave\Countries;
use Flutterwave\AuthModel;

class TransactionTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $merchantKey = "tk_tdyrSMQo8a";
    $apiKey = "tk_NabYp2XjZ6G9WwdFruzK";
    Flutterwave::setMerchantCredentials($merchantKey, $apiKey);
  }

  public function testEmpty() {
    $card = [
      "card_no" => "4604504564868571",
      "cvv" => "812",
      "expiry_month" => "08",
      "expiry_year" => "18"
    ];
    $resp = Card::charge($card, 1000, uniqid(), Currencies::NAIRA, Countries::NIGERIA, AuthModel::NOAUTH, "test");
    $data = $resp->getResponseData();
    return $data['data']['transactionreference'];
  }

  /**
   *
   * @depends testEmpty
   */
  public function testTransactionStatus($ref) {
    echo("hehhe".$ref);
    $resp = Transaction::status($ref);
    $data = $resp->getResponseData();
    print_r($data);
    $this->assertEquals("success", $data['status']);
    $this->assertTrue(!empty($data));
    $this->assertEquals("2", $data['data']['responsecode']);
    $this->assertEquals($ref, $data['data']['transactionIdentifier']);
  }
}
