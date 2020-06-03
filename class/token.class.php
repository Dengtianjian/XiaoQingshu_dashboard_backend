<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Token
{
  function makeToken()
  {
    global $_C;
    if (!$_C['user_id']) {
      HTTP::error("USER_IS_NOT_LOGGED_IN");
    }
    $str = $_C['user']['user_id'] . $_C['user']['user_name'] . $_C['user']['user_password'] . time();
    return sha1($str);
  }
  function saveToken($token)
  {
    global $_C;
    if (!$_C['user_id']) {
      HTTP::error("USER_IS_NOT_LOGGED_IN");
    }
    $result = Table("common_token")->insert([
      "token_content" => $token,
      "token_userid" => $_C['user_id'],
      "token_expire" => strtotime("+30days")
    ]);
    $_C['token'] = $token;
    $_C['user']['token'] = $token;
    return $result;
  }
  function refreshToken()
  {
    global $_C;
    if (!$_C['user_id']) {
      HTTP::error("USER_IS_NOT_LOGGED_IN");
    }
    $token = $this->makeToken();
    $saveResult = $this->saveToken($token);
    Table("common_token")->cleanExpireToken();
    return $saveResult;
  }
  function checkExpire($token = null)
  {
    global $_C;
    if ($token == null) {
      if (isset($_GET['token'])) {
        $token = addslashes($_GET['token']);
      } else {
        throw new Error("未获取到Token", 404);
      }
    }
    $DBToken = Table("common_token")->fetch_by_token($token);
    if (!$DBToken['token_id']) {
      return [
        "code" => 0,
        "message" => "凭证不存在"
      ];
    }
    if (time() > $DBToken['token_expire']) {
      return [
        "code" => 1,
        "message" => "凭证已过期"
      ];
    }
    if ($DBToken['token_expire'] - 604800 < time()) {
      $_C['user_id'] = $DBToken['user_id'];
      $this->refreshToken();
    }

    $_C['user_id'] = $DBToken['token_userid'];
    $_C['token'] = $DBToken['token_content'];
    $_C['user']['token'] = $DBToken['token_content'];

    return true;
  }
}
