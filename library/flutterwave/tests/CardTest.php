<?php
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\AuthModel;
use Flutterwave\Currencies;
use Flutterwave\Countries;

class CardTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    Flutterwave::setMerchantCredentials("tk_tdyrSMQo8a", "tk_NabYp2XjZ6G9WwdFruzK");
  }

  public function testTokenize() {
    $card = [
      "card_no" => "4716542503552752",
      "cvv" => "578",
      "expiry_month" => "01",
      "expiry_year" => "19"
    ];

    $resp = Card::tokenize($card, AuthModel::BVN, Flutterwave::SMS, "22254824829");
    // Card::tokenize($card, $authModel, $validateOption);
    $this->assertTrue($resp->isSuccessfulResponse());
  }

  public function testCharge() {
    $card = [
      "card_no" => "4716542503552752",
      "cvv" => "578",
      "expiry_month" => "01",
      "expiry_year" => "19"
    ];
    $resp = Card::charge($card, 1000, "1202", Currencies::NAIRA, Countries::NIGERIA, AuthModel::NOAUTH, "test", "");
    $this->assertTrue($resp->isSuccessfulResponse());
  }

  public function testPreAuth() {
    $resp = Card::preAuthorize("sCUDY8CgKF0Vf8j4893", 1000, Currencies::NAIRA);
    $this->assertTrue($resp->isSuccessfulResponse());
  }

  public function testCapture() {
    $resp = Card::capture("FLW00291153", "1471253330566", 1000, Currencies::NAIRA);
    $this->assertTrue($resp->isSuccessfulResponse());
  }
}
