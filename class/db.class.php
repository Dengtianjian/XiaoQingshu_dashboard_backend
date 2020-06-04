<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

include_once includeClass("database");
$Database = new Database();

class DB
{
  /**
   * 根据数组拼接出字符串
   *
   * @param string $char 键值链接得字符
   * @param array $keys 键数组
   * @param array $values 值数组 值数组长度和键数组长度一样
   * @param string $keySymbol 包围键的字符
   * @param string $valueSymbol 包围值的字符
   * @return array 拼接后的字符串数组
   */
  static function charSplice($char, $keys, $values, $keySymbol = '`', $valueSymbol = '\'')
  {
    $result = [];
    foreach ($keys as $index => $key) {
      array_push($result, "{$keySymbol}{$key}{$keySymbol}{$char}{$valueSymbol}{$values[$index]}{$valueSymbol}");
    }
    return $result;
  }
  static function charSpilt($char, $values, $symbol = "'")
  {
    foreach ($values as &$item) {
      $item = "{$symbol}{$item}{$symbol}";
    }
    return implode("$char", $values);
  }
  static function query($sql, $params = [])
  {
    if ($sql == "") {
      return;
    }
    global $Database;
    $sql = $Database->replaceParams($sql, $params);

    $result = $Database->query($sql, $params);
    return $result;
  }
  static function lastInsertId()
  {
    global $Database;
    return $Database->lastInsertId();
  }
  static function fetch($sql, $params = [])
  {
    return self::query($sql, $params);
  }
  static function fetch_first($sql, $params = [])
  {
    $result = self::fetch($sql, $params);
    if (count($result) > 0) {
      return $result[0];
    } else {
      return [];
    }
  }
  static function update($tableName, $data, $conditionSql)
  {
    $setSql = DB::charSplice("=", array_keys($data), array_fill(0, count($data), "?"), "`", "");
    $setSql = implode(",", $setSql);
    $sql = "UPDATE `&t` SET {$setSql} $conditionSql";
    $params = array_merge([
      $tableName
    ], array_values($data));
    return self::query($sql, $params);
  }
  static function jsonset_update($tableName, $data, $conditionSql = "")
  {
    $setValue = [];
    foreach ($data as $index => $item) {
      $keys = array_keys($item);
      $values = array_values($item);
      $valueStr = self::charSplice(",", $keys, $values, '\'');
      array_push($setValue, "JSON_SET($index," . implode(",", $valueStr) . ")");
    }
    $result = self::charSplice("=", array_keys($data), $setValue, '`', '');
    $JSONSETStr = implode(",", $result);
    $sql = "UPDATE `&t` SET $JSONSETStr $conditionSql;";
    return self::query($sql, [
      $tableName
    ]);
  }
  /**
   * 删除记录
   *
   * @param string $tableName 表名称
   * @param string $conditionSql 条件语句
   * @param array $params 语句参数
   * @return boolean 删除结果
   */
  static function delete($tableName, $conditionSql, $params = [])
  {
    $sql = "DELETE FROM `&t` $conditionSql";
    array_unshift($params, $tableName);
    return self::query($sql, $params);
  }

  static function quote($str, $symbol = "'", $noarray = false)
  {
    if (is_string($str) || is_int($str) || is_float($str)) {
      return "{$symbol}{$str}{$symbol}";
    }
    if (is_array($str)) {
      if ($noarray === false) {
        foreach ($str as $v) {
          $v = self::quote($v, true);
        }
        return $str;
      } else {
        return "''";
      }
    }

    if (is_bool($str)) {
      return $str ? "1" : "0";
    }

    return "''";
  }

  /**
   * 字段组合
   *
   * @param string $field 字段名称
   * @param any $val 字段值
   * @param string $glue 链接语句
   * @return string
   */
  static function field($field, $val, $glue = "=")
  {
    if (is_array($val)) {
      $glue = $glue == 'notin' ? 'notin' : 'in';
    } elseif ($glue == 'in') {
      $glue = '=';
    }

    switch ($glue) {
      case '=':
        return $field . $glue . "'$val'";
        break;
      case '-':
      case '+':
        return $field . '=' . $field . $glue . self::quote((string) $val);
        break;
      case '|':
      case '&':
      case '^':
        return $field . '=' . $field . $glue . self::quote($val);
        break;
      case '>':
      case '<':
      case '<>':
      case '<=':
      case '>=':
        return $field . $glue . self::quote($val);
        break;

      case 'like':
        return $field . ' LIKE(' . self::quote($val) . ')';
        break;

      case 'in':
      case 'notin':
        $val = $val ? implode(',', self::quote($val)) : '\'\'';
        return $field . ($glue == 'notin' ? ' NOT' : '') . ' IN(' . $val . ')';
        break;

      default:
        throw new Error('Not allow this glue between field and value: "' . $glue . '"');
    }
  }
}
