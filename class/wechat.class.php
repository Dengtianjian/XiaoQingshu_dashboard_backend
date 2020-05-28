<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

$commonSet = Table("common_set");

class Wechat
{

  static function requestAccessToken()
  {
    global $_C;
    $result = HTTP::request("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$_C['wechat_appid']['value']}&secret={$_C['wechat_appsecret']['value']}");
    $result = json_decode($result, true);
    return $result;
  }
  static function refreshAccessToken($force = false)
  {
    global $commonSet, $_C;
    if ($force == false) {
      if (self::checkAccessTokenExpire() === false) {
        return true;
      }
    }

    $requestResult = self::requestAccessToken();
    if ($requestResult['errcode']) {
      throw new Error($requestResult['errmsg'], $requestResult['errcode']);
    }

    $expire = time() + $requestResult['expires_in'];
    $update = time();
    $result = $commonSet->update_by_name("access_token", [
      "set_content" => [
        "$.value" => $requestResult['access_token'],
        "$.expire" => $expire,
        "$.update" => $update,
        "$.expire_in" => $requestResult['expires_in'],
      ]
    ], " WHERE `set_name`='access_token'");
    $_C['access_token']['value'] = $requestResult['access_token'];
    $_C['access_token']['expire'] = $expire;
    $_C['access_token']['update'] = $update;
    $_C['access_token']['expire_in'] = $requestResult['expires_in'];
    return $result;
  }
  static function checkAccessTokenExpire()
  {
    global $_C;
    $result = DB::fetch_first("SELECT * FROM `&t` WHERE `set_name`='access_token'", [
      "common_set"
    ]);
    $result['set_content'] = json_decode($result['set_content'], true);
    $result = array_merge($result, $result['set_content']);
    unset($result['set_content']);
    if ($result['value'] == 0 || (int) $result['expire'] < time() || (int) $result['expire'] < time() - 500 || (int) $result['update'] + (int) $result['expire_in'] < time() - 500) {
      return true;
    }
    return false;
  }
}
