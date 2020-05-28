<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class HTTP
{
  static function request($URL, $data = [], $method = "GET", $header = [], $https = true, $timeout = 5)
  {
    $method = strtoupper($method);
    $ch = curl_init();
    $header = array_merge([
      "content-type" => "application/json"
    ], $header);
    $headerString = charSplice(": ", array_keys($header), array_values($header), '', '');
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    if ($method != "GET") {
      $options[CURLOPT_CUSTOMREQUEST] = $method;
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      if ($header['content-type'] == "application/json") {
        $data = json_encode($data);
      }
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerString);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }
  static function post($url, $data, $header = [])
  {
    $result = HTTP::request($url, $data, "POST", $header);
    return $result;
  }
  static function response($data, $statusCode, $code)
  {
    http_response_code($statusCode);
    $data = [
      "statusCode" => $statusCode,
      "code" => $code,
      "data" => $data,
      "time" => time()
    ];
    print_r(json_encode($data));
    exit();
  }
  static function result($data)
  {
    return self::response($data, 200, 200000);
  }
  static function error($data, $code, $statusCode = 400)
  {
    return self::response($data, $statusCode, $code);
  }
}
