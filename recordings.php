<?php
  require_once(dirname(__FILE__) . "/config.php");

  // AJAX-JQUERY Controller Actions
//  if ($_POST["action"] == "delete") {
  // Controller Action - Upcoming Recordings	
//  } else if ($_GET["action"] == "upcoming") {
  // Controller Action - Schedule Recordings
//  } else if ($_GET["action"] == "schedule") {
  // Controller Action - Index / list Recordings
//  } else {
    // Display recordings and/or filtered-searched recordings
//  }

?>

<?php include "header.php" ?>


<?php
  $sql    = "select * from recording";
  $result = db_query($DBH, $sql);
  if ($result !== false) {
    foreach ($result as $row) {
	  echo $row['station_id'] . '-' . $row['program_id'] . '-' . $row['start_time'] . '-' . $row['duration'];
    }		
  } else {
	 // no data returned
	 echo "No data returned for sql: $sql \n";
  } 
?>

<?php include "footer.php" ?>