<?php

require_once("./core.php");

$Modules = [];

$module = addslashes($_GET['module']);

$password = "123456";
$passPeppered = hash_hmac("sha256", $password, $USERPASS['slat']);
$passHashed = password_hash($passPeppered, PASSWORD_BCRYPT);
$hash = '$2y$10$KnKlctFi3PK6ImHaIqbdxOrXc4AMWTfFTPhckWrPZhu7E4Hm5G/FK';
if (password_verify($passPeppered, $hash)) {
  echo "matches";
} else {
  echo "no";
}
