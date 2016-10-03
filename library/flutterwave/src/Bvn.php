<?php
namespace Flutterwave;

use Flutterwave\FlutterEncrypt;

/**
* interacts with the bvn api on flutterwave
*/
class Bvn {

  //@var array resources
  private static $bvnResources = [
    "staging" => [
      "verify" => "http://staging1flutterwave.co:8080/pwc/rest/bvn/verify/",
      "validate" => "http://staging1flutterwave.co:8080/pwc/rest/bvn/validate/",
      "resendOtp" => "http://staging1flutterwave.co:8080/pwc/rest/bvn/resendotp/"
    ],
    "production" => [
      "verify" => "https://prod1flutterwave.co:8181/pwc/rest/bvn/verify/",
      "validate" => "https://prod1flutterwave.co:8181/pwc/rest/bvn/validate/",
      "resendOtp" => "https://prod1flutterwave.co:8181/pwc/rest/bvn/resendotp/"
    ]
  ];

  /**
  * use verify to confirm the customer with the bvn
  * is the owner of the bvn
  * @param string $bvn
  * @param string $optOption can be voice or sms defaults to sms
  * @return ApiResponse
  */
  public static function verify($bvn, $otpOption = Flutterwave::SMS) {
    FlutterValidator::validateClientCredentialsSet();
    $resource = self::$bvnResources[Flutterwave::getEnv()]["verify"];
    $encryptedOtpOption = FlutterEncrypt::encrypt3Des($otpOption, Flutterwave::getApiKey());
    $resp = (new ApiRequest($resource))
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->addBody("otpoption", $encryptedOtpOption)
              ->addBody("bvn", FlutterEncrypt::encrypt3Des($bvn, Flutterwave::getApiKey()))
              ->makePostRequest();
    return $resp;
  }

  /**
   * validate the otp sent to the bvn owners phone
   * @param string $bvn used in verify call
   * @param string $otp otp sent to the bvn owners phone
   * @param string $transactionRef the transaction reference in the verify response data
   */
  public static function validate($bvn, $otp, $transactionRef) {
    FlutterValidator::validateClientCredentialsSet();
    $resource = self::$bvnResources[Flutterwave::getEnv()]["validate"];
    $encryptedBvn = FlutterEncrypt::encrypt3Des($bvn, Flutterwave::getApiKey());
    $encryptedRef = FlutterEncrypt::encrypt3Des($transactionRef, Flutterwave::getApiKey());
    $encryptedOtp = FlutterEncrypt::encrypt3Des($otp, Flutterwave::getApiKey());

    $resp = (new ApiRequest($resource))
              ->addBody("bvn", $encryptedBvn)
              ->addBody("transactionreference", $encryptedRef)
              ->addBody("otp", $encryptedOtp)
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->makePostRequest();
    return $resp;
  }

  /**
   * resend otp if bvn owner did not get the first otp message
   * @param string $transactionRef transaction reference from verify response
   * @param string $otpOption can be sms or voice. default is sms
   */
  public static function resendOtp($transactionRef, $otpOption = Flutterwave::SMS) {
    FlutterValidator::validateClientCredentialsSet();
    $encryptedRef = FlutterEncrypt::encrypt3Des($transactionRef, Flutterwave::getApiKey());
    $encryptedOption = FlutterEncrypt::encrypt3Des($otpOption, Flutterwave::getApiKey());
    $resource = self::$bvnResources[Flutterwave::getEnv()]["resendOtp"];
    $resp = (new ApiRequest($resource))
              ->addBody("validateoption", $encryptedOption)
              ->addBody("transactionreference", $encryptedRef)
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->makePostRequest();
    return $resp;
  }
}
