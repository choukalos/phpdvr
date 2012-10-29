<?php
  require_once(dirname(__FILE__).'/../config.php');
  $scanfile = dirname(__FILE__) . '/../logs/scan0.log';

  $hdhomerun = new hardware($DB,$HDHOMERUN_PATH, $LOG_DIR);
//  $hdhomerun->scan_channels('ffffffff',0);
  $hdhomerun->scan_channels('ffffffff',0,$scanfile);
//  $hdhomerun->setup();

?>
