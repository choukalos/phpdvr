#!/usr/bin/php
<?php
  // PHP script that records and stores video file to web drive
  //
  // usage   record.php deviceid tunner channel program minduration savename
  //         called by cronjob

  // require 
  require_once(dirname(__FILE__).'/config.php');

  // clean up the argv array for php and optionally the program name
  if (array_shift($argv) == "php") array_shift($argv);

  $device_id   = array_shift($argv);
  $tuner       = array_shift($argv);
  $channel     = array_shift($argv);
  $prog        = array_shift($argv);
  $minduration = array_shift($argv);
  $savename    = ereg_replace("[^A-Za-z0-9_-]", "_",ereg_replace(":","-",array_shift($argv)));

  if (empty($device_id) || empty($channel) || empty($prog) || empty($minduration) || empty($savename)) {
	echo "Syntax: \n";
	echo "  record.php deviceid tuner# channel# program# minsduration savename \n";
	echo "  \n";
    return false;
  }

  echo " devid = " . $device_id . " tuner = " . $tuner . " channel " . $channel . " program " . $prog;
  echo " for " . $minduration . " mins ";
  echo " filename: " . $savename . "\n";

  // Run recording code
  $savename     = $RECORDING_DIR . $savename . ".ts";
  $current_time = time();
  echo " ... Setting up tuner " . $HDHOMERUN_PATH . " id: " . $device_id . "\n";
  $hdhomerun = new hardware($DB, $HDHOMERUN_PATH, $LOG_DIR, $device_id);
  echo " ... Mark file as recorded in DB\n";
  $sql = "select * from recording where filename = '" . $savename . "' and channel = " . $channel;
  $sql.= " and channelMinor = " . $prog;
  $result = $DB->fetch_all($sql);
  $row    = $result[0];
  $sql = "insert into recorded (program_id, station_id, start_time, title, series, ";
  $sql.= "subtitle, filename) values ('" . $row['program_id'] . "',";
  $sql.= $row['station_id'] . ",'" . $row['series'] . "','" . $row['start_time'] . "','";
  $sql.= $row['subtitle'] . "'";
  $result = $DB->execute($sql);
  echo " ... Starting Recording \n";
  $hdhomerun->do_recording($tuner, $channel, $prog, $savename, $minduration);
  $sql = "delete from recording where id = " . $row['id'];
  $result = $DB->execute($sql);
  echo " ... Finished Recording \n";

?>