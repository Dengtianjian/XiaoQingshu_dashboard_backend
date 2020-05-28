<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Cloud
{
  private static $callCount = 0;
  static function callFunction($name, $method, $params = [])
  {
    global $_C;
    self::$callCount++;
    $params['method'] = $method;
    $result = HTTP::request("https://api.weixin.qq.com/tcb/invokecloudfunction?access_token={$_C['access_token']['value']}&env={$_C['wechat_env']['value']}&name={$name}", $params, "POST");
    $result = json_decode($result, true);
    if ($result['errcode']) {
      if (self::$callCount == 2) {
        self::$callCount = 0;
        return $result;
      }
      Wechat::refreshAccessToken(true);
      return self::callFunction($name, $method, $params);
    }
    $result['resp_data'] = json_decode($result['resp_data']);
    self::$callCount = 0;
    return $result;
  }
  /**
   * 请求
   *
   * @param string $method 执行的操作
   * @param array $params 参数
   * @return any 执行的结果
   */
  static function request($method, $params)
  {
    global $_C;
    $params = array_merge([
      "env" => $_C['wechat_env']['value']
    ], $params);
    $result = HTTP::post("https://api.weixin.qq.com/tcb/{$method}?access_token={$_C['access_token']['value']}", $params);
    return json_decode($result, true);
  }
}
