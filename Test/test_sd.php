<?php

  require_once(dirname(dirname(__FILE__)).'/config.php');
  require_once(dirname(dirname(__FILE__)).'/inc/schedules_direct.php');
  
  $sd   = new schedules_direct($SD_USER,$SD_PASS, $DB);
  $data = $sd->fetch(0);   // try 14 later on...
  $sd->update($data);

?>