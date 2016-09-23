## Bin Check Example
A Bin check is an operation that can return a card details from the first 6 digits of the card number.

```PHP
use Flutterwave\Bin;
use Flutterwave\Flutterwave;

//merchantKey and apiKey can be found in your flutter developer console
//env can be production or staging depending on your stage of development
$merchantKey = ""; //can be found on flutterwave dev portal
$apiKey = ""; //can be found on flutterwave dev portal
$env = "staging"; //this can be production when ready for deployment
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$first6digits = "763537";
$result = Bin::check($ipAddress);
//$result is an instance of ApiResponse class which has
//methods like getResponseData(), getStatusCode(), getResponseCode(), isSuccessfulResponse()
```
