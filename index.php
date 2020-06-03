<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
header("Content-type: application/json;charset=utf-8");
header('Content-language: cn');

require_once("./core.php");

//把 $_GET $_POST $_REQUEST 合并在一起
$_REQUEST = $_POST = $_GET = array_merge($_GET, $_POST, $_REQUEST);

//API 模块
$Modules = [
  "user"
];

//需要 验证凭证的方法
$verifyMethods = [
  "verify"
];

$module = addslashes($_GET['module']); //获取请求的模块名称
//判断是否存在 请求的模块
if (!in_array($module, $Modules)) {
  HTTP::error(null, "MODULES_DOES_NOT_EXISTS");
}

//加载 请求的模块文件
include_once("./module/{$module}.php");
//实例化 请求的模块文件
$moduleInstance = new $module();
//获取 请求的模块所执行的方法
$requestMethod = addslashes($_GET['method']);
//判断 所执行的方法是否存在请求的模块里
if (!in_array($requestMethod, $moduleInstance->methods)) {
  HTTP::error(null, "MODULES_METHOD_DOES_NOT_EXISTS");
}

//验证token
if (isset($_GET['token']) || in_array($requestMethod, $verifyMethods)) {
  if (!isset($_GET['token']) && in_array($requestMethod, $verifyMethods)) {
    HTTP::error([], "USER_IS_NOT_LOGGED_IN");
  }
  $token = addslashes($_GET['token']);
  include_once includeClass("token");
  $Token = new Token();
  $result = $Token->checkExpire($token);
  if (isset($result['code'])) {
    switch ($result['code']) {
      case 0:
        HTTP::error("凭证不存在", 401, 4010001);
        break;
      default:
        HTTP::error("凭证已过期", 401, 4010002);
    }
  }
}

unset($_GET['method'], $_GET['module']);
$responseResult = $moduleInstance->{$requestMethod}();
Response($responseResult);
