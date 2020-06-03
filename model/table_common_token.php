<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

include_once includeClass("table"); //数据表类

class Table_common_token extends Table
{
  public $name = "common_token";
  function cleanExpireToken()
  {
    return $this->delete("WHERE `token_expire`<&n", [
      time()
    ]);
  }
  function fetch_by_token($token)
  {
    return DB::fetch_first("SELECT * FROM `&t` WHERE `token_content`=&s", [
      $this->name,
      $token
    ]);
  }
}
