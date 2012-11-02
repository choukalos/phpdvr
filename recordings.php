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
<!-- Manually add hardcoded video player for testing purposes -->
<video width="1024" height="750" controls="controls">
  <source src="./recordings/test.ts"  type="video/mpeg2">  <!-- webkit video is .MOV -->
  <source src="./recordings/test.mov" type="video/mp4">
  <source src="./recordings/test.ogg" type="video/ogg">
  <source src="./recordings/test.webm" type="video/webm">
	 <object data="./recordings/test.mp4" width="320" height="240">
	   <embed src="./recordings/test.ts" width="320" height="240">
	 </object>	
  Your browser does not support the HTML5 video tag please try the latest Safari, Firefox or Chrome browsers.
</video>

<?php include "footer.php" ?>