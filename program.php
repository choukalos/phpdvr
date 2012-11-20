<!-- Program Modal: assumes sent with program id -->
<?php
  require_once(dirname(__FILE__)."/config.php");
  // Code to parse Program ID and pull info from DB to populate modal data
  // Also needs code to AJAX post data back to DB is recording is selected
  if ($_GET['id']) {
    $program_id  = $_GET['id'];
    $station_id  = $_GET['station_id'];
    $program_time= $_GET['time'];
  } else {
    // Post 
    $program_id  = $_POST['id'];
    $station_id  = $_POST['station_id'];
    $program_time= $_POST['time'];
    $record_opt  = $_POST['record'];
  }

//echo "<p>Caught : $program_id on $station_id at $program_time with opt $record_opt</p>\n";
//var_dump($_POST);

  $sql  = "select * from pvr_programs where program_id = '" . $program_id . "' and station_id = " . $station_id;
  $sql .= " and `time` = '" . $program_time . "'";
  $result = $DB->fetch_all($sql);
  $row    = $result[0];
  if (! $_GET['id']) {
    // POST so handle recording scheduler
    $channel     = $row["device_channel"];
    $channelMinor= $row["device_channelMinor"];
	$schedule_manager = new schedule_manager($DB, $RECORDING_DIR);
	$schedule_manager->record($record_opt, $program_time, $channel, $channelMinor, $row);
  }
  // Now handle page output
?>

<?php include "header.php" ?>

<li>ID: <?php echo $row['program_id']; ?></li>
<li>Title: <?php echo $row['title']; ?></li>
<li>Subtitle:  <?php echo $row['subtitle']; ?></li>
<li>Description: <?php echo $row['description']; ?></li>
<li>Original Air Date: <?php echo $row['orignalAirDate']; ?></li>
<?php if ($row['series'] != "") {
  echo "<li>Series: " . $row['series'] . "</li>";
  echo "<li>Episode#: " . $row['syndicatedEpisodeNumber'] . "</li>";	
}
?>
<li>Show Type: <?php echo $row['showType']; ?></li>
<li>Color Code: <?php echo $row['colorCode']; ?></li>
<br />
<?php
  if($row['recording']) {
     echo '<span class="label label-important">Marked for Recording</span>';
  }
  if($row['season_pass']) {
	echo '<span class="label label-important">Season Pass</span>';
  }
?>

<br />
<br />
<!-- need to wrap in a form to do an ajax submit back to program.php to setup recording/etc... -->
<form method="post" action="program.php">
  <label class="radio"> <input type="radio" id='record' name='record' value='no'   <?php if(!($row['recording']) and !($row['season_pass'])) { echo "checked"; } ?> >Do Not Record </label>
  <label class="radio"> <input type="radio" id='record' name='record' value='once' <?php if(($row['recording']) && !($row['season_pass'])) { echo "checked"; } ?> >Record Once</label>
  <label class="radio"> <input type="radio" id='record' name='record' value='season' <?php if($row['season_pass']) { echo "checked"; } ?>'>Always Record</label>
  <input type='hidden' name='id' id='id' value="<?php echo $program_id ?>">
  <input type='hidden' name='station_id' id='station_id' value ="<?php echo $station_id ?>">
  <input type='hidden' name='time' id='time' value="<?php echo $program_time ?>">
  <a class="btn btn-primary" href="index.php">Back</a>
  <input type='submit' value="Save" class="btn">	
</form>	
	
<?php include "footer.php" ?>