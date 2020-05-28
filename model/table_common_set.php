<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

include_once includeClass("table"); //数据表类

class Table_common_set extends Table
{

  private $pk = "set_name";
  private $name = "common_set";

  function fetch_by_name($setName)
  {
    return DB::fetch("SELECT * FROM &t WHERE `set_name`=&s", [
      $this->name, $setName
    ]);
  }

  function update_by_name($setName, $data)
  {
    return DB::jsonset_update($this->name, $data, " WHERE `set_name`='{$setName}'");
  }
}
