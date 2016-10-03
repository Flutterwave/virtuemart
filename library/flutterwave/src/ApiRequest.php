<?php
namespace Flutterwave;

use GuzzleHttp\Client;

/**
* responsible for building and sending request
* to flutterwave server
*/
class ApiRequest {
  // private $headers = array('Content-Type' => 'application/json');
  private $data = array();
  private $url;

  function __construct($url) {
    $this->url = $url;
  }

  /**
  * responsible for adding form data to request
  * @param string $name
  * @param string $value
  * @return $this
  */
  public function addBody($name, $value) {
    $this->data[$name] = $value;
    return $this;
  }

  /**
  * responsible for sending post request to the server
  * @return ApiResponse
  */
  public function makePostRequest() {
    // $response = \Requests::post($this->url, $this->headers, json_encode($this->data));
    $client = new Client(['timeout'  => 60]);
    $response = $client->request('POST', $this->url, [
        'json' => $this->data,
        'headers' => [
          'Content-Type' => 'application/json'
        ]
    ]);
    // Logging::getLoggerInstance()->info("response:".$response->getBody());
    return ApiResponse::parseResponse($response);
  }
}
