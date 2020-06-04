<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

/**
 * 加载类文件
 *
 * @param String $fileName 文件名称，不加.class
 * @param String? $filePath 文件路径
 * @return String 类所在的路径
 */
function includeClass($fileName, $filePath = null)
{
  if ($filePath) {
    return sprintf(C_ROOT . "{$filePath}{$fileName}.class.php");
  } else {
    return sprintf(C_ROOT . "class/$fileName.class.php");
  }
}

/**
 * 加载函数库文件
 *
 * @param String $fileName 文件名称
 * @param String? $filePath 文件路径
 * @return String 文件所在目录完整路劲
 */
function includeFun($fileName, $filePath = "")
{
  if ($filePath) {
    return sprintf(C_ROOT . "{$filePath}{$fileName}.php");
  } else {
    return sprintf(C_ROOT . "function/$fileName.php");
  }
}

function includeModel($tableName, $filePath = "")
{
  if ($filePath) {
    return sprintf(C_ROOT . "{$filePath}{$tableName}.php");
  } else {
    return sprintf(C_ROOT . "model/table_$tableName.php");
  }
}

function Table($tableName)
{
  include_once includeModel($tableName);
  $instanceName = "Table_$tableName";
  return new $instanceName();
}

/**
 * 断点输出
 *
 * @param Any $data 输出的数据
 * @return void
 */
function debug($data)
{
  header("Content-type:text/html;charset=utf-8");
  echo "<pre style='word-break:break-all;white-space: break-spaces;font-family:微软雅黑;'>";
  print_r($data);
  echo "</pre>";
  exit();
}

/**
 * 索引数组转关联数组
 *
 * @param Array $arr 转换的原始数组
 * @param String $key 新数组每一项的key值
 * @return Array
 */
function index2Assoc($arr, $key)
{
  if (count($arr) == 0) {
    return $arr;
  }
  $assoc = [];
  foreach ($arr as $item) {
    $assoc[$item[$key]] = $item;
  }
  return $assoc;
}

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
function charSplice($char, $keys, $values, $keySymbol = '`', $valueSymbol = '\'')
{
  $result = [];
  foreach ($keys as $index => $key) {
    array_push($result, "{$keySymbol}{$key}{$keySymbol}{$char}{$valueSymbol}{$values[$index]}{$valueSymbol}");
  }
  return $result;
}

/**
 * 响应请求
 *
 * @param array $data 响应的数据
 * @param integer $statusCode 响应状态码
 * @param integer $code 响应码
 * @return void
 */
function Response($data = null, $statusCode = 200, $code = 20001)
{
  HTTP::response($data, $statusCode, $code);
}

function getUserById($userId)
{
  $user = Table("user")->fetch_by_userid($userId);
  if (count($user) == 0) {
    return false;
  }
  $user = $user[0];

  $user['user_wechat_info'] = json_decode($user['user_wechat_info'], true);
  if (!$user['user_wechat_info'] || count($user['user_wechat_info']) == 0) {
    $userOpenId = $user['user_openid'];
    $wechatUserInfo = Cloud::http("db")::query("db.collection('user').where({_id:'$userOpenId'}).get()");
    if (count($wechatUserInfo['data']) > 0) {
      $wechatUserInfo = $wechatUserInfo['data'][0];
    } else {
      $wechatUserInfo = [];
    }
    Table("user")->update_by_userid($user['user_id'], [
      "user_avatar" => $wechatUserInfo['avatar_url'],
      "user_wechat_info" => json_encode($wechatUserInfo)
    ]);
    $user['user_wechat_info'] = $wechatUserInfo;
  }


  return $user;
}
