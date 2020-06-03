<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
header("Content-type: application/json;charset=utf-8");
header('Content-language: cn');

require_once("./core.php");

$_REQUEST = $_POST = $_GET = array_merge($_GET, $_POST, $_REQUEST);

$Modules = [
  "user"
];

$module = addslashes($_GET['module']);

if (!in_array($module, $Modules)) {
  HTTP::error(null, "MODULES_DOES_NOT_EXISTS");
}

include_once("./module/{$module}.php");
$moduleInstance = new $module();
$requestMethod = addslashes($_GET['method']);
if (!in_array($requestMethod, $moduleInstance->methods)) {
  HTTP::error(null, "MODULES_METHOD_DOES_NOT_EXISTS");
}

//验证token
if (isset($_GET['token'])) {
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
