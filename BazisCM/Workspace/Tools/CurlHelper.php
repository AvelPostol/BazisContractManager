<?php
namespace BazisСM\Workspace\Tools;

class CurlHelper {
  private $token;
  private $authHeader;
  private $baseUrl;
  private $requestUrl;
  private $top;
  private $ch;

  public function __construct($token) {
      $this->token = $token;
      $this->authHeader = 'Authorization: Bearer ' . $this->token;
      $this->baseUrl = 'url для доступа к базис';
      $this->requestUrl = $this->baseUrl . 'метод api';
      $this->top = 1;
      $this->ch = curl_init();
  }

  public function get() {
    $accumulatedMessages = array();

    $full_request_url = $this->requestUrl;

    curl_setopt($this->ch, CURLOPT_URL, $full_request_url);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, array($this->authHeader));

    $jsonResponse = curl_exec($this->ch);


    if ($jsonResponse === false) {
      echo 'cURL Error: ' . curl_error($this->ch);
      $continue = false; // Останавливаем цикл в случае ошибки
    } else {
      $response = json_decode($jsonResponse, true);
      /*
        обрабатываем $response
      
      if (isset($response) && !empty($response)) {
        foreach ($response['$value'] as $message) {
          $accumulatedMessages[] = $message;
        }
      }*/
    }

    curl_close($this->ch);
    return $accumulatedMessages;
  }
}

