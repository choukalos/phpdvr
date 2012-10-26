<?php
  require_once(dirname(__FILE__) . "/config.php");

  // AJAX-JQUERY Controller Actions
  if ($_GET["search"]) {
	$search_term = $_GET["search"];
	$search_term = "%" . $search_term . "%";
    // Run Search
    $sql = "select * from pvr_schedule where title like '$search_term' or subtitle like '$search_term'";
  } else {
    $search_term = null;	
  }
?>

<?php include "header.php" ?>
<form name="search_form" action="search.php" method="get" class="form-inline">
  <input type="text" name="search">	
  <input type="submit" value="Search">
</form>

<?php
  if ($search_term !== null) {
	// Run the search and return the results
	$result = db_query($DBH, $sql);
	// echo "<p>Found " . count($result, COUNT_RECURSIVE) . " programs.</p>\n"; // - doesnt work returns 1 when it should have a higher value
?>
	<table class="table table-condensed table-hover">
	<thead>
	  <th>Title</th>
	  <th>Subtitle</th>
	  <th>Channel</th>
	  <th>Rating</th>
	  <th>Flags</th>
	  <th>Time</th>
	  <th>Duration</th>
	</thead>
<?php 	  
	foreach ($result as $row) {
	  echo "<tr><a href='program.php?id=" . $row["program_id"] . "'>";
	  echo "<tr><td><a href='program.php?id=" . $row["program_id"] . "'>" . $row["title"] . "</td><td>";
	  echo $row["subtitle"] . "</td><td>" . $row[device_fccChannelNumber] . "</td><td>";
	  echo $row["tvRating"] . "</td><td>";
	  // add in the flags - dolby, new, stereo, tvRating //
	  if ($row["new"]) { echo "<span class='label label-success'>New</span> "; }
	  if ($row["stereo"]) { echo "<span class='label'>Stereo</span>"; }
	  if ($row["dolby"]) { echo "<span class='label label-info'>" . $row["dolby"] . "</span>"; }
	  if ($row["hdtv"]) { echo "<span class='label label-important'>HDTV</span>"; }
	  // finish the row
	  echo "</td><td>" . $row["time"] . "</td><td>" . $row["duration"] . " mins</td></a></tr>\n";	
	}
	echo "</table>\n";
  }
?>
<br />
<?php include "footer.php" ?>