<?php

date_default_timezone_set('America/Chicago');
$DB_HOST = "localhost";
$DB_PORT = "3306";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "webpvr";

$SD_USER = "choukalos";
$SD_PASS = "choukalos1";

$RECORDING_DIR    = dirname(__FILE__) . "/recordings/";
$APPLICATION_NAME = "WebPVR";
$FFMPEG_PATH      = "/usr/bin/ffmpeg";
$HDHOMERUN_PATH   = "/usr/local/bin/hdhomerun_config";
$RECORDING_SCRIPT = dirname(__FILE__) . "/mypvr_record.php";
$DAILY_SCRIPT     = dirname(__FILE__) . "/mypvr_daily.php";
$TEMPLATE_DIR     = dirname(__FILE__) . "/template/";

$DO_INSTALL       = dirname(__FILE__) . "/install.txt";

// Connect to Database
// Switch to PDO
// see:  http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/ for more info
if (file_exists($DO_INSTALL)) {
	// specify the database - normal operation
	try {
		$DBH = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME",$DB_USER,$DB_PASS);
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}	
} else {
	// do not specify a database - used only to setup the application
	try {
		$DBH = new PDO("mysql:host=$DB_HOST",$DB_USER,$DB_PASS);
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}	
}
//
// Require utility classes and functions
require_once(dirname(__FILE__) . "/inc/utility.php");
require_once(dirname(__FILE__) . "/inc/cron.php");
require_once(dirname(__FILE__) . "/inc/hdhomerun.php");
require_once(dirname(__FILE__) . "/inc/schedule_manager.php");
require_once(dirname(__FILE__) . "/inc/schedules_direct.php");




?>
