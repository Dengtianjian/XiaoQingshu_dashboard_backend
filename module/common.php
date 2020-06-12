<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class common
{
  public $methods = [
    "init",
  ];

  function init()
  {
    //获取用户组
    include_once includeModule("user_group");
    $userGroup = new user_group();
    $groups = $userGroup->getAllGroup();

    return [
      "userGroup" => $groups
    ];
  }
}
