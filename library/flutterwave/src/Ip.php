<?php
namespace Flutterwave;

/**
* Can be used to get details of a valid ip address
*/
class Ip {

  //@var array respresents both staging and production server url
  private static $url = [
    "staging" => "http://staging1flutterwave.co:8080/pwc/rest/fw/ipcheck/",
    "production" => "https://prod1flutterwave.co:8181/pwc/rest/fw/ipcheck/"
  ];

  /**
  * @param string ip
  * @return ApiResponse
  */
  public static function check($ip) {
    $resource = self::$url[Flutterwave::getEnv()];
    return (new ApiRequest($resource))
            ->addBody("ip", $ip)
            ->makePostRequest();
  }
  
}
