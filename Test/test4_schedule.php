<?php

  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/inc/schedule_manager.php');
  
  $mgr   = new schedule_manager();
//  $mgr->schedule('FFFFFFFF',0,22,3,'2012-09-15 00:00:00',30,"TEST_22_3_0915_000000");
  $mgr->schedule('FFFFFFFF',0,7,3,'2012-09-17 21:00:00',60,"Revolution.S01.E01")

?>