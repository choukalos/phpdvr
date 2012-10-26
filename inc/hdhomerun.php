<?php
  
//  require_once(dirname(__FILE__).'/cron.php');

  class hardware {
  
    private $device_id;
    private $num_tuners;
    private $hdhomerun_path;
    private $log_path;
    private $path;
    private $tempfile;
    private $dbobj;

    function __construct($db_obj, $hdhomerun_path, $log_path, $device_id=NULL) {
	  $this->dbobj          = $db_obj;
	  $this->hdhomerun_path = $hdhomerun_path;
	  $this->log_path       = $log_path;
	  if (is_null($device_id) || empty($device_id)) {
		$this->device_id = "FFFFFFFF";
	  } else {
		$this->device_id = $device_id;
	  }
	  $path_length     = strrpos(__FILE__, "/");
	  $this->path      = substr(__FILE__, 0, $path_length) . '/';
	  $tempfilefnm     = 'temp_' . $this->device_id . '.txt';
	  $this->tempfile = "{$this->log_path}{$tempfilefnm}";
	
	  echo "HDHOME Run config program located at " . $hdhomerun_path . " and using deviceid: " . $this->device_id . "\n";
	  return $this;
    }
    public function do_recording($tuner, $channel, $channelminor, $filename, $duration) {
	  $this->set_channel($tuner, $channel, $channelminor);
	  $this->record_file($tuner, $filename, $duration);
	  $this->shutdown_tuner($tuner);
    }
    public function scan_channels($deviceID, $tuner, $file=NULL) {
      // expect to be run once when nothing is going on.  Have to be run for each tuner available, file is for debug purposes	  
	  // clean out old data from table
	  $sql = "delete from channels where `device` = '$deviceID' and `tuner` = '$tuner'";
      $db = $this->dbobj;
	  $db->execute($sql);
	  // get scanning data either from file or from live pull
	  if ($file === NULL) {
	    $cmd = $this->hdhomerun_path . " " . $deviceID . " scan " . $tuner . " > " . $this->tempfile;
	    $this->execute($cmd);
	    $rows         = file($this->tempfile, FILE_IGNORE_NEW_LINES);
	  } else {
		echo "Using scanfile: '$file'\n";
	    $rows         = file($file, FILE_IGNORE_NEW_LINES);	
	  }
	  $inchannel    = false;
	  foreach ($rows as $value) {
	    list($type) = preg_split("/[\s]+/", $value);
	    if ($type === "SCANNING:") {
		  list($data0, $frequency, $data2, $channel) = preg_split("/[\s:,)]+/", $value);
		  $inchannel = false;
		}
		if ($type === "LOCK:") {
	      list($data0, $band, $data1, $ss, $data2, $snq, $data3, $seq) = preg_split("/[\s:()=]+/", $value);
		}
		if ($type === "PROGRAM") {
		  list($data0, $program, $remap, $callsign, $callsign_post) = preg_split("/[\s:()]+/", $value);	
		  $inchannel = true;
		}
		if ($inchannel && ($callsign !== "encrypted" && $callsign !== "control")) {
			// insert into channel scan db
			// insert id = $channel-$program, $tuner, $band, $freq, $channel, $program, $remap, $callsign, $ss, $snq, $seq
			// mysql_query($sql) or die(mysql_error());
			// update callsign with remap info?
			$sql =  "insert into channels set `device` = '$deviceID', `tuner` = '$tuner', `band` = '$band', `freq` = '$frequency', `channel` = '$channel', `channelMinor` = ";
			$sql .= "'$program', `callsign` = '$callsign', `callsignMinor` = '$callsign_post', `fcc_channel` = '$remap', `ss` = '$ss', `snq` = '$snq', `seq` = '$seq', ";
			$sql .= "`use` = 0";
		    $db->execute($sql);
            echo " ____ Added Channel: " . $remap . "\n";					
		}
	  	// end of foreach loop
	  }
	  unlink($this->tempfile);
	  // Return
	  return true;	  
    }
    public function setup() {
	  // This function sets up the system for all HDhomerun devices discovered in the network, assumes 2 tuners per device*
	  $devices = $this->discover();
	  foreach($devices as $id => $value) {
	    // Scan channels and load into db
	    echo "Scanning device " . $value . " tuner 0 - channels\n";
	    $this->scan_channels($value, 0);
	    echo "Scanning device " . $value . " tuner 1 - channels\n";
	    $this->scan_channels($value, 1);	
	  }
	  // Post processing work done now...
	  if (is_null($devices)) { return false; } else { return true; }
    }
    public function discover() {
	  $devices = array();	
	  $cmd     = $this->hdhomerun_path . " discover > {$this->tempfile}";
	  $this->execute($cmd);
	  if (file_exists($this->tempfile)) {	
	    $rows    = file($this->tempfile, FILE_IGNORE_NEW_LINES);
	    foreach($rows as $value) {
	      if (preg_match('/device ([A-Fa-f0-9]+)/', $value, $matches)) {
	        array_push($devices, $matches[1]);
	        echo "Found HDHOMERUN Device '" . $matches[1] . "' on local network\n";
	      }	
	    }
	    unlink($this->tempfile);
	  }
	  return $devices;
    }
  	private function set_channel($tuner, $channel, $pid) {
	  if ($channel != NULL) {
		 $cmd = $this->hdhomerun_path . " " . $this->device_id . " set /tuner" . $tuner . "/channel auto:" . $channel;
	  } else {
		 $cmd = $this->hdhomerun_path . " " . $this->device_id . " set /tuner" . $tuner . "/channel null";
	  }
	  $this->execute($cmd);
	  if ($pid != NULL) {
		 $cmd = $this->hdhomerun_path . " " . $this->device_id . " set /tuner" . $tuner . "/program " . $pid;  
	     $this->execute($cmd);
	  }
    }

    private function record_file($tuner, $filename, $minutes) {
	  $current_time = time();
	  $end_time     = $current_time + $minutes*60;
	  $cmd = $this->hdhomerun_path . " " . $this->device_id . " save /tuner" . $tuner . " " . $filename ;
	  $pid = $this->PsExec($cmd);
	  if ($pid === FALSE) {
		echo "[ERROR]:  Could not spawn cmd: $cmd!\n";
		return false;
	  }
	  $x = 0;
	  echo "\n  Recording ";
	  while( time() < $end_time ) {
		sleep(1);
		if ($x == 59) {
		  echo ".";
		  $x = 0;
		  if ($this->PsExists($pid) === FALSE) {
			 echo " ... FAILED! Lost recording process!\n";
			 return false;
		  }
		}
		$prog = $end_time - time();
		$x += 1;
	  }
	  $this->PsKill($pid);
	  return true;
    }

    private function shutdown_tuner($tuner) {
	  $this->set_channel($tuner,NULL,NULL);
    }

    private function execute($cmd) {
	  // execute the command
	  echo ".. Executing CMD: " . $cmd . "\n";
	  $rc = exec($cmd);
	  return $rc;
    }

    private function PsExec($command) {
	  $command = $command . " > /dev/null 2>&1 & echo $!";
	  exec($command, $op);
	  $pid = (int)$op[0];
      if ($pid!="") return $pid;
      return false;
    }

    private function PsExists($pid) {
	  $cmd = 'ps ax | sed \'s/^[ \t]*//\' | grep -v grep | cut -d" " -f1 | grep '.$pid;
	  exec($cmd, $output);
	  while( list(,$row) = each($output) ) { 
	     $row_array = explode(" ", $row); 
	     $check_pid = $row_array[0]; 
	     if($pid == $check_pid) { 
	       return true;  
	     } 
	  } 
	  return false; 
    }

    private function PsKill($pid) {
	  exec("kill -9 $pid");
	  sleep(5);
    }
    
  }




?>