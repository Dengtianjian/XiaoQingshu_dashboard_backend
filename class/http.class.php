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
      "code" => 404001
    ], //请求模块不存在
    "MODULES_METHOD_DOES_NOT_EXISTS" => [
      "message" => "模块方法不存在",
      "statusCode" => 404,
      "code" => 404002
    ],
    "USER_IS_NOT_LOGGED_IN" => [
      "message" => "用户未登录",
      "statusCode" => 401,
      "code" => 401001
    ],
    "WECHAT_CLOUD_DATABASE_QUERY_ERROR" => [
      "message" => "服务器错误",
      "statusCode" => 500,
      "code" => 500001
    ]
  ];
  static function response($data = null, $statusCode = 400, $code)
  {
    global $_C;
    if (gettype($data) == "string" && $statusCode != 200) {
      $message = $data;
      $data = [];
    } else if (gettype($data) == "array") {
      if ($data['message']) {
        $message = $data['message'];
        unset($data['message']);
        if (isset($data['data'])) {
          $data = $data['data'];
        }
      }
    }

    if (gettype($statusCode) == "string") {
      if ($data['message']) {
        $message = $data['message'];
      }
      if ($data == null || !$data['message']) {
        if (self::$errorCode[$statusCode]['message']) {
          $message = self::$errorCode[$statusCode]['message'];
        }
      }
      $code = self::$errorCode[$statusCode]['code'];
      $statusCode = self::$errorCode[$statusCode]['statusCode'];
    }


    $user = [];
    $token = null;

    if ($_C['user_id'] && $statusCode == 200) {
      $user = $_C['user'];
      $token = $_C['token'];
      unset($user['user_password']);
    }

    http_response_code($statusCode);
    $data = [
      "statusCode" => $statusCode,
      "code" => $code,
      "data" => $data,
      "time" => time(),
      "message" => $message,
      "user" => $user,
      "token" => $token
    ];
    print_r(json_encode($data));
    exit();
  }
  static function result($data = null)
  {
    if (!$data['message']) {
      $data['message'] = "请求成功";
    }
    return self::response($data, 200, 200000);
  }
  static function error($data = null, $statusCode = 400, $code = 400001)
  {
    return self::response($data, $statusCode, $code);
  }
}
