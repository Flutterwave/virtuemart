<?php namespace Flutterwave;

class Account {

  private static $resources = [
    "staging" => [
      "enquiry" => "http://staging1flutterwave.co:8080/pwc/rest/pay/resolveaccount",
      "charge" => "http://staging1flutterwave.co:8080/pwc/rest/account/pay",
      "validate" => "http://staging1flutterwave.co:8080/pwc/rest/account/pay/validate"
    ],
    "production" => []
  ];

  /**
   * resolve account name information
   * @param  string $accountNumber account number to resolve to name
   * @param  string $bankCode      bank code of account number
   * @return ApiResponse
   */
  public static function enquiry($accountNumber, $bankCode) {
    $key = Flutterwave::getApiKey();
    $accountNumber = FlutterEncrypt::encrypt3Des($accountNumber, $key);
    $bankCode = FlutterEncrypt::encrypt3Des($bankCode, $key);

    $url = self::$resources[Flutterwave::getEnv()]["enquiry"];
    $resp = (new ApiRequest($url))
              ->addBody("recipientaccount", $accountNumber)
              ->addBody("destbankcode", $bankCode)
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->makePostRequest();
    return $resp;
  }

  /**
   * charge a bank account
   * @param  string $accountNumber  account number to charge
   * @param  string $bankCode       bank code of the account to charge
   * @param  string $passCode       password
   * @param  array $paymentDetails ['amount'=>'', 'currency'=>'', 'narration'=> '', 'email'=>'', 'firstname'=>'', 'lastname'=>'']
   * @param  string $ref            reference
   * @return ApiResponse
   */
  public static function charge($accountNumber, $bankCode, $passCode, $paymentDetails, $ref) {
    $key = Flutterwave::getApiKey();
    $accountNumber = FlutterEncrypt::encrypt3Des($accountNumber, $key);
    $bankCode = FlutterEncrypt::encrypt3Des($bankCode, $key);
    $passCode = FlutterEncrypt::encrypt3Des($passCode, $key);
    $ref = FlutterEncrypt::encrypt3Des($ref, $key);
    $amount = FlutterEncrypt::encrypt3Des($paymentDetails['amount'], $key);
    $currency = FlutterEncrypt::encrypt3Des($paymentDetails['currency'], $key);
    $narration = FlutterEncrypt::encrypt3Des($paymentDetails['narration'], $key);
    $firstname = FlutterEncrypt::encrypt3Des($paymentDetails['firstname'], $key);
    $lastname = FlutterEncrypt::encrypt3Des($paymentDetails['lastname'], $key);
    $email = FlutterEncrypt::encrypt3Des($paymentDetails['email'], $key);

    $url = self::$resources[Flutterwave::getEnv()]["charge"];
    $resp = (new ApiRequest($url))
              ->addBody("narration", $narration)
              ->addBody("accountnumber", $accountNumber)
              ->addBody("bankcode", $bankCode)
              ->addBody("passcode", $passCode)
              ->addBody("amount", $amount)
              ->addBody("currency", $currency)
              ->addBody("firstname", $firstname)
              ->addBody("lastname", $lastname)
              ->addBody("email", $email)
              ->addBody("transactionreference", $ref)
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->makePostRequest();
    return $resp;
  }

  /**
   * validate an account charge
   * @param  string $otp one time password
   * @param  string $ref reference
   * @return ApiResponse
   */
  public static function validate($otp, $ref) {
    $key = Flutterwave::getApiKey();
    $otp = FlutterEncrypt::encrypt3Des($otp, $key);
    $ref = FlutterEncrypt::encrypt3Des($ref, $key);

    $url = self::$resources[Flutterwave::getEnv()]["validate"];
    $resp = (new ApiRequest($url))
              ->addBody("otp", $otp)
              ->addBody("transactionreference", $ref)
              ->addBody("merchantid", Flutterwave::getMerchantKey())
              ->makePostRequest();
    return $resp;
  }



}
