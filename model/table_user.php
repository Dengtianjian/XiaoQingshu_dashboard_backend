<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

include_once includeClass("table"); //数据表类

class Table_user extends Table
{
  public $pk = "user_id";
  public $name = "user";
  function fetch_first_by_username($username)
  {
    return DB::fetch_first("SELECT * FROM `&t` WHERE `user_name`=&s", [
      $this->name, "admin"
    ]);
  }
  function fetch_by_userid($userId)
  {
    return DB::fetch("SELECT * FROM `&t` WHERE &i ", [
      $this->name, $this->pk, DB::field("user_id", $userId)
    ]);
  }
  function update_by_userid($userid, $data)
  {
    return DB::update($this->name, $data, "WHERE `user_id`=$userid");
  }
}
