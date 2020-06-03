<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Table
{
  function insert($data, $returnId = true)
  {
    $insertKey = implode(",", array_keys($data));
    $insertValue = DB::charSpilt(",", array_values($data));
    $result = DB::query("INSERT INTO `&t`($insertKey) VALUES($insertValue) ", [
      $this->name
    ]);
    if ($returnId) {
      return DB::lastInsertId();
    }
    return $result;
  }

  function fetch_all()
  {
    return DB::fetch("SELECT * FROM `%t`", [
      $this->name
    ]);
  }

  function delete($conditionSql = "", $params = [])
  {
    return DB::delete($this->name, $conditionSql, $params);
  }
}
