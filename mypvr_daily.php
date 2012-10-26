#!/usr/bin/php
<?php
  // Script called by cron to update schedule_direct data, update season pass scheduling and run misc system tasks
  //
  require_once(dirname(__FILE__).'/config.php');
 
  echo "MYPVR_DAILY - Starting Daily Tasks ... \n";
  // Update and pull schedules direct data
  $guidedata = new schedules_direct($SD_USER, $SD_PASS, $DBH);
  $sd_data = $guidedata->fetch(0);
  // Fetch 14 days forward looking of guide data
//  $sd_data = $guidedata->fetch(14);
  $guidedata->update($sd_data);
  // Update season pass recording schedules
  $scheduler = new schedule_manager($DBH, $RECORDING_DIR);
  $scheduler->update_schedule();
  // Done
  echo "MYPVR_DAILY - End of Daily Processing ... \n";
?>
