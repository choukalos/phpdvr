<?php include 'header.php'; ?>
<?php
  // script setups the system and displays UI + buttons that enable rescan/etc of services
  // Assume this script runs (in general) only once
  require_once(dirname(__FILE__).'/config.php');
  require_once(dirname(__FILE__).'/inc/hdhomerun.php');
  require_once(dirname(__FILE__).'/inc/schedules_direct.php');
  
  if (!file_exists($DO_INSTALL)) {
    $rc = true;
    // Connect to DB and setup database
    $sql = "create database webpvr;";
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
    echo " DB CMD: " . $cmd . " \n";
    exec($cmd);
    echo "created database tables\n";
    // select the DB
	$DBH=null;
	try {
		$DBH = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME",$DB_USER,$DB_PASS);
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}
    echo "changed to $DB_NAME database\n";
    // Scan for hdhomerun devices and scan channels on each tuner
    $hdhomerun = new hardware($HDHOMERUN_PATH);
    $hdhomerun->setup();
    // Kick off initial schedules_direct scan
    $guidedata = new schedules_direct($SD_USER, $SD_PASS);
    $sd_data   = $guidedata->fetch(0);
    $guidedata->update($sd_data);
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
    // all good now...
    exec("rm {$DO_INSTALL}");
    echo "Completed Setup\n";
  } else {
    echo "System has already been setup!\n";
    echo "File: " . $DO_INSTALL . " Already exists\n";	
  }
?>
<?php include 'footer.php'; ?>