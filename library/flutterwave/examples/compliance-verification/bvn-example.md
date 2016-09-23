## Bvn Verification Example
This example will show you how to verify your users bvn.

```PHP
use Flutterwave\Bvn;
use Flutterwave\Flutterwave;

//merchantKey and apiKey can be found in your flutter developer console
//env can be production or staging depending on your stage of development
$merchantKey = ""; //can be found on flutterwave dev portal
$apiKey = ""; //can be found on flutterwave dev portal
$env = "staging"; //this can be production when ready for deployment
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$bvn = "76357363637";

//$otpOption can either be SMS or VOICE
$result = Bvn::verify($bvn, Flutterwave::SMS); //this will send otp to the telephone used for the bvn registration

//$result is an instance of ApiResponse class which has
//methods like getResponseData(), getStatusCode(), getResponseCode(), isSuccessfulResponse()
if ($result->isSuccessfulResponse()) {
  echo("We have sent an otp to your bvn");
} else {
  echo("Your bvn is incorrect please check again");
  return;
}

//after verify request is successful you need to validate with otp sent to the bvn owner
//validate the otp sent to the user
$result2 = Bvn::validate($bvn, $otp, Flutterwave::SMS);
if ($result2->isSuccessfulResponse()) {
  echo("Thank you for verifying yourself");
}

//if the user did not get an otp you can do resend otp with
//the transaction reference is a parameter in the response from Bvn::verify() call
$Bvn::resendOtp($transactionRef, Flutterwave::SMS);

$
```
