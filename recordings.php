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
  $sql    = "select * from recorded";
  $result = $DB->fetch_all($sql);
?>

<table class="table table-condensed table-hover">
  <thead>
	<th>Play</th>
    <th>Title</th>
    <th>SubTitle</th>
  </thead>

<?php 	  
	foreach ($result as $row) {
	  echo "<tr><td>STREAM</td>";
	  echo "<td><a href='program.php?id=" . $row["program_id"] . "&station_id=" . $row["station_id"];
	  echo "&time=" . $row["time"] . "'>" . $row["title"] . "</td><td>";
	  echo $row["subtitle"] . "</td><td></tr>";
	}
?>
</table>
<br />


<!-- Manually add hardcoded video player for testing purposes, note webkit video is .MOV -->
<!--
<video width="1024" height="750" controls="controls">
  <source src="./recordings/test.ts"  type="video/mpeg2">  
  <source src="./recordings/test.mov" type="video/mp4">
  <source src="./recordings/test.ogg" type="video/ogg">
  <source src="./recordings/test.webm" type="video/webm">
	 <object data="./recordings/test.mp4" width="320" height="240">
	   <embed src="./recordings/test.ts" width="320" height="240">
	 </object>	
  Your browser does not support the HTML5 video tag please try the latest Safari, Firefox or Chrome browsers.
</video>
-->
<?php include "footer.php" ?>