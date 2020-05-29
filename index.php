<?php

require_once("./core.php");

header("Access-Control-Allow-Origin:*");

$Modules = [
  "user"
];

$module = addslashes($_GET['module']);

if (!in_array($module, $Modules)) {
  HTTP::error("MODULES_DOES_NOT_EXISTS");
}

include_once("./module/{$module}.php");
$moduleInstance = new $module();
$requestMethod = addslashes($_GET['method']);
if (!in_array($requestMethod, $moduleInstance->methods)) {
  HTTP::error("MODULES_METHOD_DOES_NOT_EXISTS");
}
unset($_GET['method'], $_GET['module']);
$responseResult = $moduleInstance->{$requestMethod}();
Response(null, $responseResult);
// $password = "123456";
// $passPeppered = hash_hmac("sha256", $password, $USERPASS['slat']);
// $passHashed = password_hash($passPeppered, PASSWORD_BCRYPT);
// $hash = '$2y$10$KnKlctFi3PK6ImHaIqbdxOrXc4AMWTfFTPhckWrPZhu7E4Hm5G/FK';
// if (password_verify($passPeppered, $hash)) {
//   echo "matches";
// } else {
//   echo "no";
// }
