<?php
namespace Flutterwave;
use Flutterwave\FlutterEncrypt;
use Flutterwave\FlutterValidator;

class AccessAccount {

  /**
   * urls to endpoints on staging and production server
   * @var [type]
   */
  private static $accountResources = [
    "staging" => [
      "initiate" => "http://staging1flutterwave.co:8080/pwc/rest/recurrent/account/",
      "validate" => "http://staging1flutterwave.co:8080/pwc/rest/recurrent/account/validate/",
      "charge" => "http://staging1flutterwave.co:8080/pwc/rest/recurrent/account/charge/"
    ],
    "production" => [
      "initiate" => "https://prod1flutterwave.co:8181/pwc/rest/recurrent/account/",
      "validate" => "https://prod1flutterwave.co:8181/pwc/rest/recurrent/account/validate/",
      "charge" => "https://prod1flutterwave.co:8181/pwc/rest/recurrent/account/charge/"
    ]
  ];

  /**
   * setup a bank account for recurrent payment
   * @param  string $accountNumber account number
   * @return ApiResponse
   */
  public static function initiate($accountNumber) {
    FlutterValidator::validateClientCredentialsSet();

    $key = Flutterwave::getApiKey();
    $accountNumber = FlutterEncrypt::encrypt3Des($accountNumber, $key);

    $resource = self::$accountResources[Flutterwave::getEnv()]["initiate"];
    $resp = (new ApiRequest($resource))
              ->addBody("accountNumber", $accountNumber)
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->makePostRequest();
    return $resp;
  }

  /**
   * validate an initiated recurrent account payment
   * @param   string $ref          transactionReference from initiate method call
   * @param   string $accountNum   account number
   * @param   string $otp          otp sent to account owner
   * @param   int|double|float $billingAmount
   * @param   string $narration     narration
   * @return ApiResponse
   */
  public static function validate($ref, $accountNum, $otp, $billingAmount, $narration) {
    FlutterValidator::validateClientCredentialsSet();

    $key = Flutterwave::getApiKey();
    $ref = FlutterEncrypt::encrypt3Des($ref, $key);
    $accountNum = FlutterEncrypt::encrypt3Des($accountNum, $key);
    $otp = FlutterEncrypt::encrypt3Des($otp, $key);
    $billingAmount = FlutterEncrypt::encrypt3Des($billingAmount, $key);
    $narration = FlutterEncrypt::encrypt3Des($narration, $key);

    $resource = self::$accountResources[Flutterwave::getEnv()]['validate'];
    $resp = (new ApiRequest($resource))
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->addBody("otp", $otp)
              ->addBody("accountNumber", $accountNum)
              ->addBody("reference", $ref)
              ->addBody("billingamount", $billingAmount)
              ->addBody("debitnarration", $narration)
              ->makePostRequest();
    return $resp;
  }

  /**
   * charge a validated card
   * @param  string $token    account token
   * @param  int|double|float $amount amount to charge the account
   * @param  string $narration narration for charge
   * @return ApiResponse
   */
  public static function charge($token, $amount, $narration) {
    FlutterValidator::validateClientCredentialsSet();

    $key = Flutterwave::getApiKey();
    $token = FlutterEncrypt::encrypt3Des($token, $key);
    $amount = FlutterEncrypt::encrypt3Des($amount, $key);
    $narration = FlutterEncrypt::encrypt3Des($narration, $key);

    $resource = self::$accountResources[Flutterwave::getEnv()]["charge"];
    $resp = (new ApiRequest($resource))
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->addBody("accountToken", $token)
              ->addBody("billingamount", $amount)
              ->addBody("debitnarration", $narration)
              ->makePostRequest();
    return $resp;
  }
}
