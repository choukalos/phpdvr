<?php require_once(dirname(__FILE__).'/config.php'); ?>

<?php include 'header.php'; ?>

<?php
  // script setups the system and displays UI + buttons that enable rescan/etc of services
  // Assume this script runs (in general) only once
  
  // Setup functions
  function setup_create_db(&$DB, $DB_USER, $DB_PASS, $DB_HOST, $DB_NAME, $ROOT_DIR, $MYSQL_PATH) {
    // Connect to DB and setup database
    $sql = "create database $DB_NAME;";
    $DB->execute($sql);
    usleep(50000);                          // Delay 1/20th of a second so create command finishes before tables are created
//   sleep(1); 
   // Create the tables in the database
    $mysql_path_ping = exec("which mysql");
    if (empty($mysql_path_ping)) {
	  $cmd = $MYSQL_PATH;
    } else {
	  $cmd = "mysql";
    }
    $cmd .= " -h " . $DB_HOST . " -u" . $DB_USER . " ";
    if (!empty($DB_PASS)) {
	  $cmd .= " -p" . $DB_PASS . " ";
    }
    $cmd .= $DB_NAME . " < " . $ROOT_DIR . "db/db.sql ";
//    $cmd .= " < " . $ROOT_DIR . "db/db.sql ";
    echo "<p>Creating Tables: " . $cmd . " ... ";
    $rc = exec($cmd);
    echo " Got: " . $rc . "</p>\n";
    echo "<p>Finished creating database tables</p>\n";
    // select the DB and close out the old connection
    $DB->close();
    $NEWDB = null;
    return $NEWDB;
  }
  function setup_hdhomerun(&$DB, $HDHOMERUN_PATH, $LOG_DIR) {
    $hdhomerun = new hardware(&$DB, $HDHOMERUN_PATH, $LOG_DIR);
    $rc        = $hdhomerun->setup();
    if ($rc) {
      echo "<p>setup HDHomerun and scanning for channels</p>\n";
    } else {
	  echo "<p><B>ERROR:  No HDHOMERUN Systems found on your network!</p>";
    }  
    return $rc;	
  }
  function setup_schedules_direct($SD_USER, $SD_PASS, &$DB) {
	// Kick off initial schedules_direct scan
    $guidedata = new schedules_direct($SD_USER, $SD_PASS, &$DB);
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
  function setup_cronjobs($LOG_DIR,$CRON_PATH,$DAILY_SCRIPT) {
	$cronjob = new crontab_manager($LOG_DIR,$CRON_PATH);
	$cronjob->remove_cronjob("mypvr_record.php");
	$cronjob->remove_cronjob("mypvr_dailiy.php");
	// Now setup daily pvr run in cron.  Trigger at midnight everynight
	$dailyjob = "0 0 * * * /usr/bin/php $DAILY_SCRIPT > /dev/null";
	$cronjob->append_cronjob($dailyjob);
	return true;
  }
  function finalize_install($DO_INSTALL) {
    // Write file install.txt to prevent install loop from happening again 
	$fh = fopen("{$DO_INSTALL}", "w");
	fwrite($fh,"Installed\n");
	fclose($fh);
	return true;
  }
  // -------------------------------------------
  // Main code
  if (!file_exists($DO_INSTALL)) {
	// setup software and deterimne which step we're in
	// step 1 -> setup db & cronjobs
	// step 2 -> setup cron
	// step 3 -> scan hdhomerun
	// step 4 -> load schedules
	// step 5 -> link and write install.txt
	if (!isset($_GET["stage"])) {
	  echo "<p>Starting Install .... </p>\n";
	  echo "<p> Root URL: " . $ROOT_DIR . " </p>\n";
	  echo "<p> Setting up Database and Cronjobs.....\n";
	  $DB = setup_create_db($DB,$DB_USER,$DB_PASS,$DB_HOST,$DB_NAME, $ROOT_DIR, $MYSQL_PATH);	
	  $rc = setup_cronjobs($LOG_DIR,$CRON_PATH,$DAILY_SCRIPT);
	  if (!$rc) { 
		echo " FAILED!  Check database access rights!</p>\n"; 
	  } else {
		// Redirect to index, stage 2
	    header("refresh: 1; setup.php?stage=2");
	  }
	} elseif ($_GET["stage"] == 2) {
	  // HDhomerun scan
	  echo "<p> Scanning HDHOMERUN channels .....";
	  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
	  $rc = setup_hdhomerun(&$DB, $HDHOMERUN_PATH, $LOG_DIR);
	  if (!$rc) {
		echo " FAILED!  Check hdhomerun_config path to make sure it's installed!</p>\n";
	  } else {
	    // Redirect to index, stage 3	
	    header("refresh: 1; setup.php?stage=3");
	  }	
	} elseif ($_GET["stage"] == 3) {
	  // schedules direct scan
	  echo "<p> Pulling scheduling data from schedules direct...";
	  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
	  $rc = setup_schedules_direct($SD_USER, $SD_PASS, &$DB);
	  if (!$rc) {
		 echo " FAILED!  Check schedules direct login credentials and antenna setup<p>\n";
	  } else {
		 // Redirect to index, stage 4
		header("refresh: 1; setup.php?stage=4");
	  }
	} elseif ($_GET["stage"] == 4) {
	  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
      $rc = finalize_install($DO_INSTALL);
      echo "<p>creating $DO_INSTALL file to flag system install has been completed </p>";
	  echo "<p><b>Completed Setup</b></p>\n";
	  echo "<br/><p>You will be redirected in 5 seconds</p>\n";
	  header("refresh: 5; index.php");	
    }
    // end of setup logic
  } else {
    echo "System has already been setup!\n";
    echo "File: " . $DO_INSTALL . " Already exists\n";	
  }
?>

<?php include 'footer.php'; ?>