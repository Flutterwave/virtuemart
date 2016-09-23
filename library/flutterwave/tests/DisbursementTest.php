<?php
use Flutterwave\Disbursement;
use Flutterwave\Countries;
use Flutterwave\Currencies;
use Flutterwave\Flutterwave;

class DisbursementTest extends PHPUnit_Framework_TestCase {
  public function testSend() {
    $amount = 1000;
    $ref = "233456";
    $sender = "Ridwan Olalere";
    $destination = [
      "country" => Countries::NIGERIA,
      "currency" => Currencies::NAIRA,
      "bankCode" => "058",
      "recipientAccount" => "0123453241",
      "recipientName" => "Ridwan Olalere"

    ];
    $narration = "Testing";

    // Flutterwave::setMerchantCredentials("tk_tdyrSMQo8a", "tk_NabYp2XjZ6G9WwdFruzK");
    $resp = Disbursement::send($amount, $ref, $sender, $destination, $narration);

  }
}
