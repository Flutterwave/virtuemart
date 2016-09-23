# Get Paid Card Examples

## Card features
This document explains how to use Flutterwave APIs for performing operations on cards.
Supported cards include MasterCard, Visa, and Verve.
- Tokenize card
- Charge card
- Validate card
- Preauthorize
- Capture
- Refund
- Void

#### Tokenize card
Tokenization is the ability to replace all the sensitive information on a card
into a safe string token that can be used to charge the card at a later date.

```PHP
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\AuthModel;

$merchantkey = "744hdhhg"; //merchant key on flutterwave dev portal
$apiKey = "58jdjdjd"; //merchant api key on flutterwave dev portal
$env = "staging"; //can be staging or production
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$card = [
  "card_no" => "",
  "cvv" => "",
  "expiry_month" => "",
  "expiry_year" => ""
];

$authModel = AuthModel::BVN; //this tells flutterwave how to validate the user of the card is the card owner
//you can also use AuthModel::NOAUTH which does not need validate method call
$validateOption = Flutterwave::SMS; //this tells flutterwave to send authentication otp via sms
$bvn = ""; //represents the bvn number of the card owner/user
$result = Card::tokenize($card, $authModel, $validateOption, $bvn = "");

if ($result->isSuccessfulResponse()) {
  echo("Card was successfully tokenized");
}

//Important note
//when using VBVSECURECODE as the AuthModel you will need to decrypt responsehtml field in the response data
//of a successful request
//your key here is your apiKey from flutterwave dashboard
$plain = FlutterEncrypt::decrypt3Des($responsehtml, $key);
```

#### Charge card
This operation allows you to charge a card of a certain amount through flutterwave API.

```PHP
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\AuthModel;
use Flutterwave\Currencies;

$merchantkey = "744hdhhg"; //merchant key on flutterwave dev portal
$apiKey = "58jdjdjd"; //merchant api key on flutterwave dev portal
$env = "staging"; //can be staging or production
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$card = [
  "card_no" => "",
  "cvv" => "",
  "expiry_month" => "",
  "expiry_year" => "",
  "card_type" => "" //optional parameter. only needed if card was issued by diamond card
];
$custId = "76464"; your users customer id
$currency = Currencies::NAIRA; //currency to charge the card
$authModel = AuthModel::BVN; can be BVN, NOAUTH, PIN, etc
$narration = "narration for this transaction";
$responseUrl = ""; //callback url
$country = Countries::NIGERIA;
$response = Card::charge($card, $amount, $custId, $currency, $country, $authModel, $narration, $responseUrl);

//Important note
//when using VBVSECURECODE as the AuthModel you will need to decrypt responsehtml field in the response data
//of a successful request
//your key here is your apiKey from flutterwave dashboard
$plain = FlutterEncrypt::decrypt3Des($responsehtml, $key);

```
__List of supported countries and currencies for card charge__
+ NIGERIA -
NGN
USD
GBP
EURO

+ GHANA -
GHS
USD

+ UNITED STATES -
USD

+ KENYA -
KES
USD

+ United Kingdom -
GBP
USD
EURO
Hit us up to activate your desired location for you!

#### Validate card
This operation can be used to validate a card if an auth model of bvn or pin was chosen in the charge
operation. If NOAUTH was chosen then you won't need to validate the card.

```PHP
$ref = ""; //transactionReference in the Card::charge method call response data
$otp = "4444"; //opt sent to card user
$cardType = ""; //optional. Only needed to be Diamond if its a diamond bank card
$result = Card::validate($ref, $otp, $cardType = "");
if($result->isSuccessfulResponse()) {
  echo("Successful");
}
```

#### Preauthorize
This operation allows you to put a hold of an amount on a certain card without charging the card

```PHP
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\AuthModel;

$merchantkey = "744hdhhg"; //merchant key on flutterwave dev portal
$apiKey = "58jdjdjd"; //merchant api key on flutterwave dev portal
$env = "staging"; //can be staging or production
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

//this operation will put an hold on the $amount
//to perform this operation you need to have first tokenized the card
$token = ""; //token from tokenize response data
$result = Card::preAuthorize($token, $amount, $currency);
if ($result->isSuccessfulResponse()) {
  echo("successful");
}
```

#### Capture
This operation can be used to charge a card of an amount that was previously preauthorized.

```PHP
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\Currencies;

$merchantKey = ""; //can be found on flutterwave dev portal
$apiKey = ""; //can be found on flutterwave dev portal
$env = "staging"; //this can be production when ready for deployment
Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$authRef = ""; //in the preauthorize() call the response returns a reference, use it here
$transId = ""; //in the preauthorize() call the response returns a transaction id, use it here
$amount = 1000; //amount to capture.
$currency = Currencies::NAIRA;
$result = Card::capture($authRef, $transId, $amount, $currency);
if ($result->isSuccessfulResponse()) {
  echo("Hurray!");
}
```

#### Refund
This operation can be used to refund a preauthed amount.

```PHP
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\Currencies;

Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$authRef = ""; //in the preauthorize() call the response returns a reference, use it here
$transId = ""; //in the preauthorize() call the response returns a transaction id, use it here
$amount = 1000; //amount to capture.
$currency = Currencies::NAIRA;
$result = Card::refund($authRef, $transId, $amount, $currency);
if ($result->isSuccessfulResponse()) {
  echo("Hurray!");
}
```

#### Void
This operation can be used to void a preauthed amount on card.

```PHP
use Flutterwave\Card;
use Flutterwave\Flutterwave;
use Flutterwave\Currencies;

Flutterwave::setMerchantCredentials($merchantKey, $apiKey, $env);

$authRef = ""; //in the preauthorize() call the response returns a reference, use it here
$transId = ""; //in the preauthorize() call the response returns a transaction id, use it here
$amount = 1000; //amount to capture.
$currency = Currencies::NAIRA;
$result = Card::void($authRef, $transId, $amount, $currency);
if ($result->isSuccessfulResponse()) {
  echo("Hurray!");
}
```
