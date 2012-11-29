<?php
  
// how to use:  http://net.tutsplus.com/tutorials/php/managing-cron-jobs-with-php-2/
//  $crontab = new crontab_manager()
//  $new_cronjobs = array(
//    '0 0 1 * * /my/path/the_command.sh',
//    '30 8 * * 6 /my/path/the_command.sh > /dev/null 2>&1'
//	);
//  $crontab->append_cronjob($new_cronjobs);
//  $cronregex = '/my/path/the_commdn.sh';
//  $crontab->remove_cronjob($cron_regex);
//


class crontab_manager {
	private $cronfile;
	private $handle;
	private $cronexec;
	
	function __construct($tempdir = null, $cronpath = null) { 
	  if (is_null($tempdir)) {
  	    $this->path      = dirname(dirname(__FILE__)) . '/';	  	
	  } else {
		$this->path    = $tempdir;
	  }
	  if (is_null($cronpath)) {
		$this->cronexec  = "crontab";
	  } else {
	    $this->cronexec  = $cronpath;	
	  }
	  $this->handle    = 'crontab.txt';
	  $this->cronfile = "{$this->path}{$this->handle}";
//echo "CrontabMgr:  using file {$this->cronfile} with exec {$this->cronexec}\n";	
	  return $this;
	}
	public function append_cronjob($cron_jobs=NULL) {
		if (is_null($cron_jobs)) $this->error_message("Nothing to append!  Please specify a cron job or an array of cron jobs.");  
		$orig_cronfile   = $this->read_crontab();
		$append_cronfile = "echo '";          
		$append_cronfile .= (is_array($cron_jobs)) ? implode("\n", $cron_jobs) : ($cron_jobs . "\n");  
		$append_cronfile .= "'  >> {$this->cron_file}";  
		$this->exec($append_cronfile);
		$this->write_to_file();
		$this->remove_file();
		return $this;
	}
	public function remove_cronjob($cron_jobs=NULL) {
	  if (is_null($cron_jobs)) {
		$this->error_message("Nothing to remove!  Please specify a cron job or an array of cron jobs.");  
		return $this;
	  }
	  $cron_array = $this->read_crontab();
	  if (empty($cron_array)) {
		$this->error_message("Nothing to remove!  Crontab is already empty.");
		return $this;
	  } 
	  $original_count = count($cron_array);  
	    if (is_array($cron_jobs))  
	    {  
	        foreach ($cron_jobs as $cron_regex) $cron_array = preg_grep($cron_regex, $cron_array, PREG_GREP_INVERT);  
	    }  
	    else  
	    {  
	        $cron_array = preg_grep($cron_jobs, $cron_array, PREG_GREP_INVERT);  
	    }     
	    if ($original_count === count($cron_array)) {
		  $this->remove_file(); 
		} else {
		  $this->remove_crontab();
		  $this->append_cronjob($cron_array);		
		}
        return $this;
	}
	public function remove_crontab() {
		$this->exec("{$this->cronexec} -r");
		$this->remove_file();  
		return $this;
	}	
	public function remove_file() {
	  if ($this->crontab_file_exists()) $this->exec("rm {$this->cron_file}");  
	  return $this;
	}
	public function get_cronformat($datetime, $command) {
		$cron_row = $this->datetime_to_cronformat($datetime);
		$cron_row = $cron_row . " " . $command . "\n";
		return $cron_row;
	}
	public function datetime_to_cronformat($datetime) {
	   // cron format:  min (0-59), hour(0-23), day of month( 1- 31), month (1-12), day of week (0-6, 0 = sunday or names), * = wildcard...
	   // @reboot = run at reboot
	   // 0 0 * * *  dailymidnightcronjob.sh    # runs daily at midnight...
	   $str = date('i  H  j  n  w ', date_format($datetime));
	   return $str;	
	}
	private function write_to_file() {
	  if ($this->crontab_file_exists()) {
		$cmd = "{$this->cronexec} {$this->cron_file}";
	  }	else {
//	    die ("Can not write to file, no crontab.txt to use!");
	    echo "Can not write to file, no crontab.txt to use!\n";	
	  }
	  return $this->exec($cmd);
	}
	private function read_crontab() {
		$rc = $this->exec("{$this->cronexec} -l > {$this->cron_file}");
		if (preg_match("/no crontab/i",$rc)) {
			$this->initialize_crontab();
			$this->exec("{$this->cronexec} -l > {$this->cron_file}");
		}
		$cron_array = file($this->cron_file, FILE_IGNORE_NEW_LINES);
		return $cron_array;
	}
	private function crontab_file_exists() {
		return file_exists($this->cron_file);  
	}
	private function error_message($error) {
//		die("ERROR: {$error} ");
		echo "ERROR: {$error} \n";
	}
	private function initialize_crontab() {
		$this->exec("echo ' ' > {$this->cron_file}; {$this->cronexec} {$this->cron_file}");
		return true;
	}
	private function exec($cmd) {
//	  try {
		$rc = exec($cmd);
//	   	if (! $rc) throw new Exception("Unable to execute cmd {$cmd}.\n");
//	  } catch (Exception $e) {
//	    $this->error_message($e->getMessage());	
//	  }
	
	  return $rc;	
	}
}


?>