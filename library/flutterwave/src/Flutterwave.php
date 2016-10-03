<?php
namespace Flutterwave;

/**
* A base class for setting commonly needed values for
* flutterwave api integration
*
* Class Flutterwave
*
* @package Flutterwave
*/
class Flutterwave {

  const VOICE = "voice";
  const SMS = "sms";

  //@var string The merchant key to be used for requests
  private static $merchantKey;

  //@var string The api key to be used for encryption
  private static $apiKey;

  //@var string represents the development environment as either staging or production
  private static $env = "staging";

  /**
  * construct the Flutterwave client
  * @param string $merchantKey
  * @param string $apiKey
  */
  public static function setMerchantCredentials($merchantKey, $apiKey, $env = "staging") {
    if (empty($merchantKey)) {
      throw new \InvalidArgumentException("Merchant key can not be empty");
    }

    if (empty($apiKey)) {
      throw new \InvalidArgumentException("Api key can not be empty");
    }

    if (empty($env) || ($env !== "staging" && $env !== "production")) {
      throw new \InvalidArgumentException("env variable can only be `staging` or `production`");
    }

    self::$merchantKey = $merchantKey;
    self::$apiKey = $apiKey;
    self::$env = $env;
  }

  /**
  * get the merchant key
  * @return string $merchantKey
  */
  public static function getMerchantKey() {
    return self::$merchantKey;
  }

  /**
  * get the api key
  * @return string $apiKey
  */
  public static function getApiKey() {
    return self::$apiKey;
  }

  /**
  * get env name. It can either be staging or production
  * @return string $env
  */
  public static function getEnv() {
    return self::$env;
  }
}
