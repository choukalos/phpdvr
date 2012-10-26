<?php include 'header.php'; ?>
<?php
  // script setups the system and displays UI + buttons that enable rescan/etc of services
  // Assume this script runs (in general) only once
  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/inc/hdhomerun.php');
  require_once(dirname(__FILE__).'/inc/schedules_direct.php');
  
  if (!file_exists($DO_INSTALL)) {
	echo "<p>Starting Install .... </p>\n";
	echo "<p> Root URL: " . $ROOT_DIR . " </p>\n";
    $rc = true;
    // Connect to DB and setup database
    $sql = "create database $DB_NAME;";
		try {
			$DBH->exec($sql);
		}
		catch(PDOException $e)
        {
			echo $e->getMessage();
        }
    // Create the tables in the database
    $cmd = "mysql -u" . $DB_USER . " ";
    if ($DB_PASS != "") {
	  $cmd .= " -p" . $DB_PASS . " ";
    }
    $cmd .= $DB_NAME . " < " . dirname(__FILE__) . "/db/db.sql ";
//    echo " DB CMD: " . $cmd . " \n";
    exec($cmd);
    echo "<p>created database tables</p>\n";
    // select the DB
	$DBH=null;
	try {
		$DBH = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME",$DB_USER,$DB_PASS);
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}
    echo "<p>changed to $DB_NAME database</p>\n";
    // Scan for hdhomerun devices and scan channels on each tuner
    $hdhomerun = new hardware($HDHOMERUN_PATH);
    $hdhomerun->setup();
    echo "<p>setup HDHomerun and scanning for channels</p>\n";
    // Kick off initial schedules_direct scan
    $guidedata = new schedules_direct($SD_USER, $SD_PASS);
    $sd_data   = $guidedata->fetch(0);
    $guidedata->update($sd_data);
    echo "<p>Fetched listing data and loaded into database</p>\n";
    // Map schedules direct stations to HDhomerun stations
    $sql =  " update channels inner join all_stations on (substring_index(channels.fcc_channel,'.',1) ";
    $sql .= "= all_stations.channel and substring_index(substring_index(channels.fcc_channel,'.',-1),'.',1) ";
    $sql .= "= all_stations.channelMinor) set channels.station_id = all_stations.station_id ";
		try {
			$DBH->exec($sql);
		}
		catch(PDOException $e)
        {
			echo $e->getMessage();
        }
    echo "<p>Mapped device channels to listing service channels</p>\n";
    // all good now...
    exec("rm {$DO_INSTALL}");
    echo "<p><b>Completed Setup</b></p>\n";
    echo "<br/><p>You will be redirected in 5 seconds</p>\n";
    // header("refresh: 5; index.php");
  } else {
    echo "System has already been setup!\n";
    echo "File: " . $DO_INSTALL . " Already exists\n";	
  }
?>
<?php include 'footer.php'; ?>