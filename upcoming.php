<?php
  // Include important files
  require_once(dirname(__FILE__) . "/config.php");
  // Include page specific functions
  $sql = "select * from pvr_upcoming";
  $result = $DB->fetch_all($sql);
?>
<?php include "header.php"; ?>
<h3>Upcoming Recordings</h3>
<table class="table table-condensed table-hover">
  <thead>
    <th>Series</th>
    <th>Title</th>
    <th>Subtitle</th>
    <th>Time</th>
    <th>Minutes</th>
    <th>Channel</th>
  </thead>
<?php 	  
	foreach ($result as $row) {
	  echo "<tr><td>" . $row["series_title"] . "</td>";
	  echo "<td><a href='program.php?id=" . $row["program_id"] . "&station_id=" . $row["station_id"];
	  echo "&time=" . $row["start_time"] . "'>" . $row["title"] . "</a></td>";
	  echo "<td>" . $row["subtitle"] . "</td>";
	  echo "<td>" . $row["start_time"] . "</td>";
	  echo "<td>" . $row["duration"] . "</td>";
	  echo "<td>" . $row["device_fccChannelNumber"] . "</td></tr>";	
	}
	echo "</table>\n";
?>
<br />

<?php include "footer.php"; ?>
