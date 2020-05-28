<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Database
{
  private $DB;
  function __construct()
  {
    $this->connect();
  }
  private function connect()
  {
    global $DBCONFIG;
    $dsn = "{$DBCONFIG['type']}:host={$DBCONFIG['host']};dbname={$DBCONFIG['name']}";
    $PDO = new PDO($dsn, $DBCONFIG['user'], $DBCONFIG['pass']);
    $this->DB = $PDO;
    $this->DB->exec("SET NAMES {$DBCONFIG['charset']}");
  }
  function replaceParams($sql, &$params)
  {
    global $DBCONFIG;
    preg_match_all('/&\w{1}/', $sql, $matchResult);
    $matchResult = $matchResult[0];
    foreach ($matchResult as $index => $item) {
      switch ($item) {
        case '&t':
          $sql = str_replace($item, $DBCONFIG['prefix'] . $params[$index], $sql);
          unset($params[$index]);
          break;
        case '&s':
          $sql = str_replace($item, "'?'", $sql);
          break;
        case '&i':
          if (gettype($params[$index]) == "array") {
            foreach ($params[$index] as &$paramItem) {
              if (gettype($paramItem) == "string") {
                $paramItem = "'$paramItem'";
              }
            }
            $replaceStr = implode(",", $params[$index]);
            $params[$index] = $replaceStr;
          }
          $sql = str_replace($item, $params[$index], $sql);
          break;
        default:
          $sql = str_replace($item, "?", $sql);
          break;
      }
    }
    return $sql;
  }
  function query($sql, $params = [])
  {
    $sth = $this->DB->prepare($sql);
    $sth->execute($params);
    if ($sth->errorCode() != 0) {
      throw new Error($sth->errorInfo()[2], $sth->errorInfo()[1]);
    }
    if ($sth->columnCount() > 0) {
      return $sth->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $sth->rowCount();
    }
  }
}
