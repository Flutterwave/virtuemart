<?php
namespace Flutterwave;

class FlutterValidator {

  public static function validateClientCredentialsSet() {
    if (empty(Flutterwave::getMerchantKey()) || empty(Flutterwave::getApiKey())) {
      throw new \Exception("You need to set merchant credentials first. Do `Flutterwave::setMerchantCredentials(merchantKey, apiKey);`");
    }
  }
}
