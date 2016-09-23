## Disbursement Example
With Flutterwave disbursement API, you can make payment to any financial institution in supported
countries.

```PHP
use Flutterwave\Disbursement;
use Flutterwave\Flutterwave;
use Flutterwave\Banks;
use Flutterwave\Countries;
use Flutterwave\Currencies;

//merchantKey and apiKey can be found in your flutter developer console
//env can be production or staging depending on your stage of development
$merchantKey = ""; //can be found on flutterwave dev portal
$apiKey = ""; //can be found on flutterwave dev portal
$env = "staging"; //this can be production when ready for deployment
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$uniqueRef = "73646474"; //any unique reference you want to use
$amount = 10000; //amount to send
$sender = "Ridwan Olalere"; name of the sender
$result = Banks::allBanks(); // this will return all banks with bank codes
$banks = array();
if ($result->isSuccessful()) {
  $response = $result->getResponseData();
  $banks = $response["data"]["058"]; //the 058 represents the response data
}

$destination = array(
  "bankCode" => $banks['058'], //the 058 represents the bank code
  "recipientAccount" => "0983736454",
  "recipientName" => "Ayo Ayo",
  "country" => Countries::NIGERIA,
  "currency" => Currencies::NAIRA
);
$narration = "any description for this payment";

$result = Disbursement::send($amount, $uniqueRef, $sender, $destination, $narration);
//$result is an instance of ApiResponse class which has
//methods like getResponseData(), getStatusCode(), getResponseCode(), isSuccessfulResponse()

if ($result->isSuccessfulResponse()) {
  echo("I have successfully sent the money to you.");
}
```
