<?php
  require_once(dirname(__FILE__).'/../config.php');

  // Overwrite DB object to ensure it's working correctly
  // Note just uses what's in the DB to schedule a recording
  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
  $series = "EP01430682";
  $title  = "KXAN News Today";

  $schedule_manager = new schedule_manager($DB, $RECORDING_DIR);
  $schedule_manager->record_season($series, $title);

  // Check to see if recording has been set
  $sql = "select * from series_pass where series = '" . $series . "'";
  $result = $DB->fetch_all($sql);
  echo "Found in series pass:\n";
  var_dump($result);
  echo "Checking recordings to see if setup / cronjobbed\n";
  $sql = "select * from recording where series = '" . $series . "'";
  $result = $DB->fetch_all($sql);
  echo "Found in recording:\n";
  var_dump($result);

?>