<?php
  require_once(dirname(__FILE__).'/../config.php');
  $scanfile = dirname(__FILE__) . '/../logs/scan0.log';

  // Overwrite DB object
  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
  $hdhomerun = new hardware($DB,$HDHOMERUN_PATH, $LOG_DIR);
//  $hdhomerun->scan_channels('ffffffff',0);
  $hdhomerun->scan_channels('ffffffff',0,$scanfile);
  $hdhomerun->setup();

?>
