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
  private static $errorCode = [
    "MODULES_DOES_NOT_EXISTS" => [
      "message" => "模块不存在",
      "statusCode" => 404,
      "code" => 40401
    ], //请求模块不存在
    "MODULES_METHOD_DOES_NOT_EXISTS" => [
      "message" => "模块方法不存在",
      "statusCode" => 404,
      "code" => 40402
    ]
  ];
  static function response($statusCode, $code, $data = null)
  {
    if (gettype($data) == "string") {
      $message = $data;
      $data = [];
    } else if (gettype($data) == "array") {
      if ($data['message']) {
        $message = $data['message'];
        unset($data['message']);
      }
    }

    if (gettype($code) == "string") {
      if ($data == null || !$data['message']) {
        if (self::$errorCode[$code]['message']) {
          $message = self::$errorCode[$code]['message'];
        }
      }
      $statusCode = self::$errorCode[$code]['statusCode'];
      $code = self::$errorCode[$code]['code'];
    }


    http_response_code($statusCode);
    $data = [
      "statusCode" => $statusCode,
      "code" => $code,
      "data" => $data,
      "time" => time(),
      "message" => $message
    ];
    print_r(json_encode($data));
    exit();
  }
  static function result($data = null)
  {
    if (!$data['message']) {
      $data['message'] = "请求成功";
    }
    return self::response(200, 200000, $data);
  }
  static function error($code, $statusCode = 400, $data = null)
  {
    return self::response($statusCode, $code, $data);
  }
}
