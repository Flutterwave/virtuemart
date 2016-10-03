<?php
namespace Flutterwave;

class ApiResponse {
  private $isSuccessfulResponse = false;
  private $statusCode;
  private $responseCode = "00";
  private $responseMessage;
  private $responseData;
  private $requiresValidation = false;

  /**
  * return server response status code
  * @return int statusCode
  */
  public function getStatusCode() {
    return $this->statusCode;
  }

  /**
  * return server response code if  status code not 500
  * @return int responseCode
  */
  public function getResponseCode() {
    return $this->responseCode;
  }

  /**
  * return server response status code
  * @return boolean isSuccessfulResponse
  */
  public function isSuccessfulResponse() {
    return $this->isSuccessfulResponse;
  }

  /**
   * some request to server will be successful but requires
   * a validation step to complete action. use requiresValidation to
   * check if you need to validate
   * @return Boolean true or false
   */
  public function requiresValidation() {
    return $this->requiresValidation;
  }

  /**
  * return server response status code
  * @return string responseMessage
  */
  public function getResponseMessage() {
    return $this->responseMessage;
  }

  /**
  * return server response status code
  * @return array responseData
  */
  public function getResponseData() {
    return $this->responseData;
  }

  /**
  * parses response from Requests response into
  * Flutter wave ApiResponse
  * @param Request_Response
  * @return ApiResponse
  */
  public static function parseResponse($response) {
    $resp = new ApiResponse();
    $resp->statusCode = $response->getStatusCode();
    if ($resp->statusCode < 500) {
      $data = json_decode($response->getBody(), true);
      if (isset($data['data']['responseCode'])) {
          $resp->responseCode = $data['data']['responseCode'];
      }
      if (isset($data['data']['responsecode'])) {
          $resp->responseCode = $data['data']['responsecode'];
      }
      if (isset($resp->responseCode) && $resp->responseCode === "02") {
          $resp->requiresValidation = true;
      }
      if ($data['status'] === "success" && $resp->statusCode === 200
          && ($resp->responseCode === "00" || $resp->responseCode === "0" || $resp->responseCode === "02")) {
          $resp->isSuccessfulResponse = true;
      }

      $resp->responseData = $data;
      if (isset($data['data']['responsemessage'])) {
          $resp->responseMessage = $data['data']['responsemessage'];
      }
    }
    return $resp;
  }
}
