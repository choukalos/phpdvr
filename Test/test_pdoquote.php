<?php
  require_once(dirname(__FILE__) . '/../config.php');

  $DB     = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );

  $quoted = $DB->quote(null);
  echo "Try quoting a null variable: $quoted\n";
  
  $quoted = $DB->quote("I'm a bad bad string ... there I said it - lolo");
  echo "Quoted bad string: $quoted\n";

?>