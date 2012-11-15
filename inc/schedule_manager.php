<?php
//   require_once(dirname(dirname(__FILE__)).'/config.php');
   require_once(dirname(__FILE__).'/cron.php');

   class schedule_manager {

	 private $tuner_array;
	 private $available;
	 private $crontab;
	 private $dbobj;
	
     function __construct(database $dbobj, $recording_directory) {
	   $this->crontab          = new crontab_manager();
	   $this->recording_script = dirname(dirname(__FILE__)) . "/mypvr_record.php";
	   $this->dbobj            = $dbobj;
	   $this->recording_dir    = $recording_directory;
	   return $this;
     }
     public function record ($program, $start_time, $channel, $channelMinor) {
	   // Function called from programs.php to record a single program	
       $filename               = $this->determine_filename($program, $start_time);	
       list($deviceid, $tuner) = $this->get_free_tuner($start, $program['duration'], $channel, $channelMinor);       
	   // Add the cronjob entry
	   $crontabtime   = $this->get_crontab_time($starttime);
	   $crontabentry  = $crontabtime . " /usr/bin/php " . $this->recording_script . " $deviceid $tuner ";
	   $crontabentry .= "$channel $channelMinor " . $program['duration'] . " $filename > /dev/null";
	   // Add the DB entry
	   $sql  = "insert replace into recording set program_id = '" . $program['program_id'] ."'";
	   $sql .= ", series = '" . $program['series'] . "', series = '" . $program['series'] . "'";
	   $sql .= ", start_time = '" . $starttime . "', duration = " . $program['duration'];
	   $sql .= ", filename = '" . $filename . "', deviceid = '" . $deviceid . "'";
	   $sql .= ", tuner = $tuner, channel = $channel, channelMinor = $channelMinor ";
	   $this->dbobj->execute($sql);
	   return true;
     }

     public function cancel_schedule($savename) {
	   // this function is called to cancel a scheduled recording in cron
	   // keyed off savename
	   $crontabregex = "/" . $savename . "/";
	   $this->crontab->remove_cronjob($crontabregex);
	   // remove from DB
	
     }
     public function schedule_season() {
	   // call this to schedule a seasons pass for a series
	
     }
     public function cancel_season() {
	   // call this to cancel a seasons pass for a series
	
     }
     public function update_schedule() {
	   // This function is called daily based on updated SD data
	   // to update the schedule for season passes where new episodes are detected
	
     }
    private function get_free_tuner($starttime, $program_data, $channel, $channelMinor) {
      // This function assigns device and tuner
      $sql     = "select * from channels where channel = $channel and channelMinor = $channelMinor";      
      $results = $this->dbobj->fetch_all($sql);

      // Imp later -> need to scan installed devices

      $deviceid = 'FFFFFFFF';
      $tuner    = 0;
      // return free device and tuner      
	  return array($deviceid, $tuner);
    }
    private function determine_filename($program_data, $start_time) {
	   // This function returns what the saved filename should be for a recording
	   if ($program_data['series'] === '') {
	      // Not a series - a one off program, but be careful their could be repeats of the title:subtitle combo...	
	      if ($program_data['subtitle']) {
		    $name = $program_data['title'] . $program_data['subtitle'];
	      } else {
		    $name = $program_data['title'] . "(" . $start_time . ")";
	      }
	   } else {
	      // This is a series, name based of title:subtitle (assuming there is a subtitle filled in )
          if (is_null($program_data['subtitle']) or ($program_data['subtitle'] === '')) {
	        if ($program_data['syndicatedEpisodeNumber'] !== '' or is_null($program_data['syndicatedEpisodeNumber']) ) {
		      $name = $program_data['title'] . " Episode " . $program_data['syndicatedEpisodeNumber'];
	        } else {
	          $name = $program_data['title'] . " Episode " . $start_time;
	        }
          } else {
	        $name = $program_data['title'] . " - " . $program_data['subtitle'];
          }     
	   }
	   // end of naming logic, cleanse name to ensure file has no special characters or spaces

	   // return name
	   return $name;   
     }
     private function get_crontab_time($mysql_format) {
	   // This function takes a mysql format datetime and converts to a cronjob row
	   $date        = DateTime::createFromFormat("Y-m-d H:i:s",$mysql_format);
		print_r($date);
		echo "\n";
	   $min         = $date->format("i");  // minutes with leading zeros (hope that's okay)
	   $hour        = $date->format("G");  // 24 hour format without leading zeros
	   $dayofmonth  = $date->format("j");  // 1 to 31, no leading zeros
	   $month       = $date->format("m");  // 1 to 12, no leading zeros
	   $dayofweek   = $date->format("w");  // note 0-6 for Sun-Sat
	   $crontabtime = "$min $hour $dayofmonth $month $dayofweek";	
	   return $crontabtime;
     }
     private function epoctime_to_datetime($epoctime) {
	   return date('r', $epoctime);
     }
     private function datetime_to_epoctime($datetime) {
	   return date('F jS', strtotime($datetime));
     }	
	 // End of Class
   }

?>