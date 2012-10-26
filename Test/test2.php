<?php
  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/inc/hdhomerun.php');
  $scanfile = dirname(__FILE__) . '/logs/scan0.log';

  $hdhomerun = new hardware($HDHOMERUN_PATH);
//  $hdhomerun->scan_channels('ffffffff',0,$scanfile);
  $hdhomerun->setup();

?>
