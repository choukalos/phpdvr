<?php
  // hack of setup script

  require_once(dirname(__FILE__).'/../config.php');
  $scanfile = dirname(__FILE__) . '/../logs/scan0.log';
  // overwrite DB, assuming database has been created and tables/views setup
  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
  // Setup HDHOMERUN
  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
  $hdhomerun = new hardware($DB,$HDHOMERUN_PATH, $LOG_DIR);
  $hdhomerun->scan_channels('ffffffff',0, $scanfile);
  $hdhomerun->setup();
  // Get SD data
  $guidedata = new schedules_direct($SD_USER, $SD_PASS, $DB);
  $sd_data = $guidedata->fetch(1);
  $guidedata->update($sd_data);
  // Map SD to device channels
  $sql =  " update channels inner join all_stations on (substring_index(channels.fcc_channel,'.',1) ";
  $sql .= "= all_stations.channel and substring_index(substring_index(channels.fcc_channel,'.',-1),'.',1) ";
  $sql .= "= all_stations.channelMinor) set channels.station_id = all_stations.station_id ";
  $DB->execute($sql);
  // turn off config file flag
  system("touch ../logs/install.txt");
  // Done
  echo "Done with setup....\n";

?>