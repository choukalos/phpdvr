<?php

  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/inc/schedules_direct.php');
  
  $sd   = new schedules_direct($SD_USER,$SD_PASS);
  $data = $sd->fetch(14);
  $sd->update($data);

?>