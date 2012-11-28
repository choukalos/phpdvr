<?php
  require_once(dirname(__FILE__).'/../config.php');

  // Overwrite DB object to ensure it's working correctly
  // Note just uses what's in the DB to schedule a recording
  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
  //
  $program_id   = "EP000809390008";
  $station_id   = 25147;
  $program_time = "2012-11-28 09:00:00";

  // Query and set schedule 
  $sql  = "select * from pvr_programs where program_id = '" . $program_id . "' and station_id = " . $station_id;
  $sql .= " and `time` = '" . $program_time . "'";
  $result = $DB->fetch_all($sql);
  $row    = $result[0];
  $channel     = $row["device_channel"];
  $channelMinor= $row["device_channelMinor"];

  echo "Recording on $channel - $channelMinor at $program_time\n";
//  var_dump($row);
//  echo "\n";

  $schedule_manager = new schedule_manager($DB, $RECORDING_DIR, $LOG_DIR);
  $schedule_manager->record_once($program_time, $channel, $channelMinor, $row);

  $rc = $schedule_manager->is_recorded($program_id, $channel, $channelMinor, $program_time);
  echo "IS recording reports $rc !\n";

  // Check to see if recording has been set
  $sql = "select * from recording where program_id = '" . $program_id . "' and start_time = '" . $program_time . "'";
  $result = $DB->fetch_all($sql);
  echo "Manual check - Found in recordings:\n";
  var_dump($result);

?>