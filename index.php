<?php
  // Include important files
  require_once(dirname(__FILE__) . "/config.php");
  require_once(dirname(__FILE__) . "/inc/tvgrid.php");
  // Include page specific functions
  // get current time
  $now = $_SERVER['REQUEST_TIME'];	  // returns epoc time :(
  $now_time = epoctime_to_datetime($now);
  $now_date = date('Y-m-d', $now);
  // Get Date
  if ($_GET["date"]) {
    $beg_date = $_GET["date"];	
  } else {
    $beg_date = date('Y-m-d',$now);	
  }
  // Get Time
  if ($_GET["time"]) {
    $beg_hour = $_GET["time"];	
  } else {
	$current_hour = date('H', $now);
    if ($current_hour < 4) { $beg_hour = 0; }
    elseif ($current_hour < 8) { $beg_hour = 4; }
    elseif ($current_hour < 12) { $beg_hour = 8; }
    elseif ($current_hour < 16) { $beg_hour = 12;}
    elseif ($current_hour < 20) { $beg_hour = 16;}
    else { $beg_hour = 20; }
  }
  $end_hour = $beg_hour + 4;
  $beg_time = $beg_date . ' ' . sprintf('%02s',$beg_hour) . ":00:00";
  $end_time = $beg_date . ' ' . sprintf('%02s',$end_hour) . ":00:00";

//  echo "Time is now: " . $now_time . " beg time: " . $beg_time . " looking till " . $end_time . " (beg_hour: $beg_hour -> $end_hour for beg_date: $beg_date)\n";
  // Build the query  
  $sql = "select * from pvr_schedule where time >= '$beg_time' and time <= '$end_time' order by device_fccChannelNumber asc, time asc; ";
  $tv_grid = new tvgrid($beg_hour);
  // Build the drop down query
  $sql_dropdown       = "select cast(time as date) from pvr_schedule where time >= '$now_date' group by cast(time as date)";
  $dropdown_selection = db_fetch_all($DBH,$sql_dropdown);
?>
<?php include "header.php"; ?>

            <h3>Guide Data <small>(today: <?php echo $now_time; ?>)</small></h3>
            <!-- use progress bars for guide listing -->
            <div>
	          <form name="guidenavigation" action="index.php" method="get" class="form-inline">
		        Date:
		        <select name="date">
			      <?php
			        foreach($dropdown_selection as $opt) {
				      echo "<option value='" . $opt[0] . "'";
				      if ($opt[0] === $beg_date) { echo " selected"; }
				      echo ">" . $opt[0] . "</option>\n";
			        }      
			      ?>
			    </select>
				<input type="radio"    name="time" value="0" >00:00
				<input type="radio"    name="time" value="4" >04:00
				<input type="radio"    name="time" value="8" >08:00
				<input type="radio"    name="time" value="12">12:00
				<input type="radio"    name="time" value="16">16:00
				<input type="radio"    name="time" value="20">20:00
	            <input type="submit"   class="btn" value="Go">
	          </form>
	        </div>

<?php
   // call the SQL and add the guide data
   $result = db_query($DBH, $sql);
   if ($result !== false) {
	 echo $tv_grid->generate($result);
   } else {
	 echo "No data returned for sql: $sql \n";
   }
   // finished generating the tv grid
?>

<?php include "footer.php"; ?>
