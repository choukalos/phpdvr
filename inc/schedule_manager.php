<?php
//   require_once(dirname(dirname(__FILE__)).'/config.php');
   require_once(dirname(__FILE__).'/cron.php');

   class schedule_manager {

	 private $tuner_array;
	 private $available;
	 private $crontab;
	 private $dbobj;
	
     function __construct(database $dbobj, $recording_directory, $log_directory, $cron_path = null) {
	   $this->crontab          = new crontab_manager($log_directory, $cron_path);
	   $this->recording_script = dirname(dirname(__FILE__)) . "/mypvr_record.php";
	   $this->dbobj            = $dbobj;
	   $this->recording_dir    = $recording_directory;
	   return $this;
     }
     public function record ($recording_type, $start_time, $channel, $channelMinor, array $program) {
	
	   switch ($recording_type) {
		 case 'once':
		   if ($program['season_pass']) { 
			  $this->cancel_season($program['series']); 
		   } elseif (! $program['recording']) {
		      $this->record_once($start_time, $channel, $channelMinor, $program);
		   } else {
			  // do nothing - already set to record.
		   }
		   break;
		 case 'season':
		   if ($program['recording']) { 
			  $this->cancel_recording($start_time, $channel, $channelMinor, $program); 
		   } elseif (! $program['season_pass']) {
		      $this->record_season($program['series'], $program['title']);
		   } else {
			  // Already set to record a seson pass - do nothing.
		   }
		   break;
		 case 'no':
           if ($program['season_pass']) {
	          $this->cancel_season($program['series']);
           } elseif ($program['recording']) {
	          $this->cancel_recording($start_time, $channel, $channelMinor, $program);
           }		   
		 default:
	   }
	   return;
	
     }
     public function record_once ($start_time, $channel, $channelMinor, array $program) {
	   // see if program already queued up for recording
	   if ( !$this->is_recorded($program["program_id"], $channel, $channelMinor, $start_time) ) {
	     // then queue up program for recording
         $filename               = $this->determine_filename($program, $start_time);	
         list($deviceid, $tuner) = $this->get_free_tuner($start_time, $program['duration'], $channel, $channelMinor);       
	     // Add the cronjob entry
	     $crontabtime   = $this->get_crontab_time($start_time);
	     $crontabentry  = $crontabtime . " /usr/bin/php " . $this->recording_script . " $deviceid $tuner ";
	     $crontabentry .= "$channel $channelMinor " . $program['duration'] . " $filename > /dev/null";
	     $this->crontab->append_cronjob($crontabentry);
	     // Add the DB entry
	     $sql  = "insert into recording set program_id = '" . $program['program_id'] ."'";
	     $sql .= ", station_id = " . $program['station_id'];
	     $sql .= ", series = '" . $program['series'] . "'" ;
	     $sql .= ", start_time = '" . $start_time . "', duration = " . $program['duration'];
	     $sql .= ", title = '" . $program['title'] . "', subtitle = '" . $program['subtitle'];
	     $sql .= "', filename = '" . $filename . "', deviceid = '" . $deviceid . "'";
	     $sql .= ", tuner = $tuner, channel = $channel, channelMinor = $channelMinor ";
	     $rc   = $this->dbobj->execute($sql);
	     if ($rc == false) {
		   return false;
	     }
	   }
	   // if skip or successfully queued return true
	   return true;
     }

     public function cancel_schedule($savename) {
	   // this function is called to cancel a scheduled recording in cron
	   // keyed off savename
	   $crontabregex = "/" . $savename . "/";
	   $this->crontab->remove_cronjob($crontabregex);
	   // remove from DB
	
     }
     public function record_season($series, $title) {
	   // call this to schedule a seasons pass for a series
	   $sql  = "insert ignore into series_pass set series = '" . $series . "', title = '";
	   $sql .= $title . "'";
	   $rc   = $this->dbobj->execute($sql);
	   $this->update_schedule($series);
	   return true;
     }
     public function cancel_recording($start_time, $channel, $channelMinor, array $program) {
	   // call this to cancel a single recording
	   $filename = $this->determine_filename($program, $start_time);
	   $this->cancel_schedule($filename);
       //
	   $sql = "select id from recording where start_time = '" . $start_time . "' and channel = ";
	   $sql.= $channel . " and channelMinor = " . $channelMinor . " and series = '" . $program['series'];
	   $sql.= "' and `filename` = '" . $filename . "'";
	   $result = $this->dbobj->fetch_all($sql);
	   $ids = array();
	   foreach ($result as $row) {
		 array_push($ids, $row['id']);
	   }
	   $sql = "delete from recording where id in (" . implode(",", $ids) . ")";
	   $result = $this->dbobj->execute($sql);
	   if ($result == false) {
		  echo "Error - could not delete recordings from recording table!\n";
		  return false;
	   }
	   return true;
     }
     public function cancel_season($series) {
	   // call this to cancel a seasons pass for a series
	   $sql    = "select * from recording where series = '" . $series . "'";
	   $result = $this->dbobj->fetch_all($sql);
	   $count  = 0;
	   foreach ($result as $row) {
	     // go through each row and pull information to delete respective recordings and cronjobs
	     $this->cancel_schedule( $row['filename'] );
	     $count += 1;
	   } 
	   $sql    = "delete from recording where series = '" . $series . "'";
	   $result = $this->dbobj->fetch_all($sql);
	   if ($result != $count or $result == false) {
 echo "Huh?  Wrong number of deleted cronjobs?! think $result -deleted--> $count\n";
	   }
	   $sql    = "delete from series_pass where series = '" . $series . "'";
	   $result = $this->dbobj->fetch_all($sql);
	   if ($result == false) {
		  return false;
	   }
	   return true;
     }
     public function update_schedule($series = null) {
	   // This function is called daily based on updated SD data
	   // to update the schedule for season passes where new episodes are detected
	   if (is_null($series)) {
		 // Called by daily program, refresh everything
		 $sql = "select * from pvr_schedule where season_pass = 1 and `time` > '" . $this->now() . "'";
	   } else {
		 // Called by record script, only refresh for a particular series
		 $sql = "select * from pvr_schedule where season_pass = 1 and series = '" . $series . "'";
		 $sql.= " and `time` > '" . $this->now() . "'";
	   }
	   $result = $this->dbobj->fetch_all($sql);
	   foreach ($result as $row) {
		 // Loop through the result rows.  For each show add a recording for it if it doesn't exist in db
		 if (! $this->is_recorded($row['program_id'], $row['device_channel'], $row['device_channelMinor'], $row['time'], true)) {
		   $this->record_once($row['time'], $row['device_channel'], $row['device_channelMinor'], $row);	
		 }
		 // Should be all good now... for both cronjobs and DB recording entries 
	   }
	   // finished updating schedule
	   return;
     }
    // This function is called to see if a recording is already marked in the recording table
    // to prevent duplicates / etc
    public function is_recorded($program_id, $channel, $channelMinor, $start_time, $series_flag=false) {
      if ($series_flag) {
	    $sql = "select * from recording where program_id = '" . $program_id . "'";
      } else {
	    $sql = "select * from recording where program_id = '" . $program_id . "' and ";
	    $sql.= "start_time = '" . $start_time . "' and channel = $channel and channelMinor = ";
	    $sql.= "$channelMinor";
	  }
	  $result = $this->dbobj->fetch_all($sql);
	  if (empty($result)) {
		return false;
	  } 
	  return true;
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
    private function determine_filename(array $program, $start_time) {
	   // This function returns what the saved filename should be for a recording
	   if ($program['series'] === '') {
	      // Not a series - a one off program, but be careful their could be repeats of the title:subtitle combo...	
	      if ($program['subtitle']) {
		    $name = $program['title'] . $program['subtitle'];
	      } else {
		    $name = $program['title'] . "(" . $start_time . ")";
	      }
	   } else {
	      // This is a series, name based of title:subtitle (assuming there is a subtitle filled in )
          if (is_null($program['subtitle']) or ($program['subtitle'] === '')) {
	        if ($program['syndicatedEpisodeNumber'] !== '' or is_null($program['syndicatedEpisodeNumber']) ) {
		      $name = $program['title'] . " Episode " . $program['syndicatedEpisodeNumber'];
	        } else {
	          $name = $program['title'] . " Episode " . $start_time;
	        }
          } else {
	        $name = $program['title'] . " - " . $program['subtitle'];
          }     
	   }
	   // end of naming logic, cleanse name to ensure file has no special characters or spaces
       // default is to add . instead of white space for file names and drop all ' and " chars
       $name = preg_replace('([\s\,\"\'\:\-]+)','.', $name);
	   // return name
	   return $name;   
     }
     private function get_crontab_time($mysql_format) {
	   // This function takes a mysql format datetime and converts to a cronjob row
	   $date        = DateTime::createFromFormat("Y-m-d H:i:s",$mysql_format);
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
     private function now() {
	   return date("Y-m-d H:i:s");
     }
	 // End of Class
   }

?>