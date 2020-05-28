<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Table
{

  private $pk = "";
  private $name = "";

  function fetch_all()
  {
    return DB::fetch("SELECT * FROM `%t`", [
      $this->name
    ]);
  }
}
