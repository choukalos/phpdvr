<?php
   class tvgrid {

	 private $grid_output;
	 private $width_hours;
	 private $last_channel;
	 private $start_hour;
	
     function __construct($start_hour, $width_hours = 4) {
	   $this->gridoutput   = null;
	   $this->width_hours  = $width_hours;
	   $this->start_hour   = $start_hour;
	   $this->last_channel = null;
	   return $this;
     }

     function generate($db_statement) {
       // setup the Top of table - station and 30 min time blocks
       $out       = $this->generate_header();
       
       foreach($db_statement as $row) {
	     if ($row['device_fccChannelNumber'] !== $this->last_channel) {
		   // start a new station
		   $out .= "</div>\n";
		   $out .= "<div class='progress'>\n";
		   $out .= "<div class='bar' style='width:20%'>" . $row['device_fccChannelNumber'] . "</div>\n";
		   $this->last_channel = $row['device_fccChannelNumber'];
	     }
	     // Add program found in row to the Grid
	     // ToDo:  chg bar-success to bar-warning (orange) or bar-danger (red) if show is scheduled to be recorded
	     $out .= "<div class='bar bar-success' style='width:" . ($row['duration'] / 30) * $this->per_width() . "%'>";
	     $out .= "<a href='program.php?id=" . $row['program_id'] . "'>" . $row['title'] . "</a></div>\n";	
       }
	   // end generate functions
	   return $out;
     }
     // HEADER - Generate TV Grid HEADER 
     //
     private function generate_header() {
	   // This function generates the TV grid header (used if footer desired)
	   $per_items = $this->width_hours * 2;
	   $per_width = ($per_items / 8) * 10;
	   $last_time = $this->start_hour . ":00";
	   $out  = "<div class='progress'>\n";
	   $out .= "  <div class='bar' style='width:20%'>Station</div>\n";
	   for($x = 1;$x < $per_items+1;$x++) {
	     $out .= "  <div class='bar' style='width:" . $per_width . "%'>";
	     if ($x % 2 == 0) {
		   $hour = $this->start_hour + floor($x / 2) . ":00";
	     } else {
		   $hour = $this->start_hour + floor($x / 2) . ":30";
	     }
	     $out .= "$last_time - $hour</div>\n";	
	     $last_time = $hour;
	   }
	   return $out;
     }
     private function per_width() {
	   $items = $this->width_hours * 2;
	   $per   = ($items/8) * 10;
       return $per;	
     }
     // End of class
  }
?>