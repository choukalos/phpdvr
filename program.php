<!-- Program Modal: assumes sent with program id -->
<?php
  require_once(dirname(__FILE__)."/config.php");
  // Code to parse Program ID and pull info from DB to populate modal data
  // Also needs code to AJAX post data back to DB is recording is selected
  if ($_GET['id']) {
    $program_id  = $_GET['id'];
    $schedule_id = $_GET['station_id'];
  } else {
    // Post 
    $program_id  = $_POST['id'];
    $schedule_id = $_POST['station_id'];
    $record_opt  = $_POST['record'];
    // Update DB based upon this....	
	$schedule_manager = new schedule_manager($DBH, $RECORDING_DIR);
//	$schedule_manager->record($program, $start_time, $channel, $channelMinor)
  }
  $sql = "select * from pvr_programs where id = '" . $program_id . "'";
?>

<?php include "header.php" ?>

<?php 
try {
   foreach ($DBH->query($sql) as $row) {
?>

<li>ID: <?php echo $row['id']; ?></li>
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
<?php 	
   }
}

catch(PDOException $e)
{
	echo $e->getMessage();
}

?>
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
  <label class="radio"> <input type="radio" name='record' value='no'   <?php if(! $row['recording']) { echo "checked"; } ?> >Do Not Record </label>
  <label class="radio"> <input type="radio" name='record' value='once' <?php if(($row['recording']) && !($row['season_pass'])) { echo "checked"; } ?> >Record Once</label>
  <label class="radio"> <input type="radio" name='record' value='season <?php if($row['season_pass']) { echo "checked"; } ?>'>Always Record</label>
  <input type='hidden' name='id' id='id' value="<?php echo $program_id ?>">
  <input type='hidden' name='station_id' id='station_id' value ="<?php echo $station_id ?>">
  <a class="btn btn-primary" href="index.php">Back</a>
  <input type='submit' value="Save" class="btn">	
</form>	
	
<?php include "footer.php" ?>