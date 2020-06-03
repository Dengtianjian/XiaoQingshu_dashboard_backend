<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

include_once includeClass("table"); //数据表类

class Table_user extends Table
{
  private $pk = "user_id";
  private $name = "user";
  function fetch_first_by_username($username)
  {
    return DB::fetch_first("SELECT * FROM `&t` WHERE `user_name`=&s", [
      $this->name, "admin"
    ]);
  }
}
