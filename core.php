<?php

define("IN_C", true);
define("C_ROOT", str_replace("\\", "/", __DIR__) . "/");

//接管 异常输出
function showException($exception)
{
  debug($exception);
}
set_exception_handler('showException');

include_once("./config.php"); //配置文件

//引入所需函数库
include_once("./function/base.php");

//引入所需类库
include_once includeClass("http"); //HTTP类
include_once includeClass("cloud"); //小程序云开发类
include_once includeClass("db"); //数据库静态类
include_once includeClass("wechat"); //微信对接类
include_once includeClass("table"); //数据表类

//全局变量
$_C = [];

//获取全部设置
$sets = DB::fetch("SELECT `set_name`,`set_content` FROM `&t`", [
  "common_set"
]);
$sets = index2Assoc($sets, "set_name");
foreach ($sets as &$set) {
  $setContent = json_decode($set['set_content'], true);
  if ($setContent) {
    $set = array_merge($set, $setContent);
  }
  unset($set['set_content']);
}
$_C = array_merge($sets, $_C);

Wechat::refreshAccessToken();
