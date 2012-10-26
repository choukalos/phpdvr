<?php require_once(dirname(__FILE__).'/config.php'); ?>

<?php include 'header.php'; ?>

<?php
  // script setups the system and displays UI + buttons that enable rescan/etc of services
  // Assume this script runs (in general) only once
  
  // Setup functions
  function setup_create_db($DB, $DB_USER, $DB_PASS, $DB_HOST, $DB_NAME, $ROOT_DIR) {
    // Connect to DB and setup database
    $sql = "create database $DB_NAME;";
    $DB->execute($sql);
    // Create the tables in the database
    $cmd = "mysql -h " . $DB_HOST . " -u" . $DB_USER . " ";
    if ($DB_PASS != "") {
	  $cmd .= " -p" . $DB_PASS . " ";
    }
    $cmd .= $DB_NAME . " < " . $ROOT_DIR . "db/db.sql ";
    echo "<p>Creating Tables: " . $cmd . " </p>\n";
    exec($cmd);
    echo "<p>Finished creating database tables</p>\n";
    // select the DB
    $DB = $DB->change_database($DB_NAME);
    echo "<p>changed to $DB_NAME database</p>\n";
    return $DB;
  }
  function setup_hdhomerun($DB, $HDHOMERUN_PATH, $LOG_DIR) {
    $hdhomerun = new hardware($DB, $HDHOMERUN_PATH, $LOG_DIR);
    $rc        = $hdhomerun->setup();
    if ($rc) {
      echo "<p>setup HDHomerun and scanning for channels</p>\n";
    } else {
	  echo "<p><B>ERROR:  No HDHOMERUN Systems found on your network!</p>";
    }  
    return $rc;	
  }
  function setup_schedules_direct($SD_USER, $SD_PASS, $DB) {
	// Kick off initial schedules_direct scan
    $guidedata = new schedules_direct($SD_USER, $SD_PASS, $DB);
    $sd_data   = $guidedata->fetch(0);
    if (is_null($sd_data)) {
	  $rc = false;
	  echo "<p><b>Can not access Schedules Direct - check user/password info!</b></p>\n";
    } else {
      $guidedata->update($sd_data);
      echo "<p>Fetched listing data and loaded into database</p>\n";
      // Map schedules direct stations to HDhomerun stations
      $sql =  " update channels inner join all_stations on (substring_index(channels.fcc_channel,'.',1) ";
      $sql .= "= all_stations.channel and substring_index(substring_index(channels.fcc_channel,'.',-1),'.',1) ";
      $sql .= "= all_stations.channelMinor) set channels.station_id = all_stations.station_id ";
      $DB->execute($sql);
      echo "<p>Mapped device channels to listing service channels</p>\n";
      $rc = true;
    }
    return $rc;
  }    
  // -------------------------------------------
  // Main code
  if (!file_exists($DO_INSTALL)) {
	// setup software
	echo "<p>Starting Install .... </p>\n";
	echo "<p> Root URL: " . $ROOT_DIR . " </p>\n";
	$DB = setup_create_db($DB,$DB_USER,$DB_PASS,$DB_HOST,$DB_NAME, $ROOT_DIR);
    $rc = setup_hdhomerun($DB, $HDHOMERUN_PATH, $LOG_DIR);
    if ($rc) {
	  $rc = setup_schedules_direct($SD_USER, $SD_PASS, $DB);
    }
    if ($rc) {
	  // all good now...
	  exec("rm {$DO_INSTALL}");
	  echo "<p><b>Completed Setup</b></p>\n";
	  echo "<br/><p>You will be redirected in 5 seconds</p>\n";
	  // header("refresh: 5; index.php");	
    }
    // end of setup logic
  } else {
    echo "System has already been setup!\n";
    echo "File: " . $DO_INSTALL . " Already exists\n";	
  }
?>

<?php include 'footer.php'; ?>