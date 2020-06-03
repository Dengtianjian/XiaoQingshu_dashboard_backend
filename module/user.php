<?php

class user
{
  public $methods = [
    "signin",
    "verify"
  ];

  function signin()
  {
    global $_C, $USERPASS;
    $username = addslashes($_POST['username']);
    $password = addslashes($_POST['password']);

    $user = Table("user")->fetch_first_by_username($username);
    $passPeppered = hash_hmac("sha256", $password, $USERPASS['slat']);
    if (!password_verify($passPeppered, $user['user_password'])) {
      return Response(4010001, "登录失败，用户名或密码错误", 401);
    }
    $_C['user_id'] = $user['user_id'];
    $_C['user'] = $user;
    include_once includeClass("token");
    $Token = new Token();
    $Token->refreshToken();
    return [
      "message" => "登录成功",
      "data" => []
    ];
  }

  function verify()
  {
    return true;
  }
}
