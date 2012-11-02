<?php
  // Pull in schedule direct data and load into mysql for scheduling/etc...
  // http://www.schedulesdirect.org
  // format:  
//  require_once(dirname(dirname(__FILE__)).'/config.php');

  class schedules_direct {

    private $data_url='http://docs.tms.tribune.com/tech/tmsdatadirect/schedulesdirect/tvDataDelivery.wsdl';
    private $db;
    private $login;
    private $password;

    function __construct($login, $password, &$db_obj) {
      $this->login    = $login;
      $this->password = $password;
      $this->db       = $db_obj;
	  return $this;
    }	
    public function update($data) {
	    // This fuction updates show schedules in the DB...
      foreach ($data->xtvd->stations->station as $i => $value) {
        $affiliate = mysql_real_escape_string( $value->affiliate );
        $name      = mysql_real_escape_string( $value->name );
        $sql       = "insert ignore into stations set `id` = '{$value->id}', `fccChannelNumber` = '{$value->fccChannelNumber}', ";
        $sql      .= "`callSign` = '{$value->callSign}', `name` = '" . $name . "', `affiliate` = '" . $affiliate ."'";

		$this->insert_data($sql);
      }

      foreach ($data->xtvd->schedules->schedule as $i => $value) {
        if(isset($value->stereo)) {$stereo = $value->stereo;} else {$stereo = '';}
        if(isset($value->hdtv)) {$hdtv = $value->hdtv;} else {$hdtv = '';}
        if(isset($value->closeCaptioned)) {$closeCaptioned = $value->closeCaptioned;} else {$closeCaptioned = '';}
        if(isset($value->tvRating)) {$tvRating = $value->tvRating;} else {$tvRating = '';}
        if(isset($value->new)) {$new = $value->new;} else {$new = '';}
        if(isset($value->dolby)) {$dolby = $value->dolby;} else {$dolby = '';}
        if(isset($value->part))  { $part_number = $value->part->number; $part_total = $value->part->total; } else { $part_number = ''; $part_total = '';}
        $sd_duration      = array("PT", "H", "M");
        $mysql_duration   = array("", ":", "");
        $clean_duration   = str_replace($sd_duration, $mysql_duration, $value->duration);
        list($hour,$min)  = preg_split('/[\:]+/',$clean_duration);
        $duration         = $hour * 60 + $min;

        $sql              = "insert ignore into `schedules` set `program_id` = '{$value->program}', `station_id` = '{$value->station}',";   
        $sql             .= "`time` = STR_TO_DATE('{$value->time}','%Y-%m-%dT%H:%i:%sZ'), `duration` = '$duration', `tvRating` = '$tvRating',";
        $sql             .= "`stereo` = '$stereo', `hdtv` = '$hdtv', `closeCaptioned` = '$closeCaptioned', `dolby` = '$dolby',";
        $sql             .= "`new` = '$new', `part_number` = '$part_number', `part_total` = '$part_total', `record` = ''";
        $this->insert_data($sql);
      }

      foreach ($data->xtvd->programs->program as $i => $value) {
        $title=mysql_real_escape_string( $value->title );
        if(isset($value->subtitle)) {$subtitle=mysql_real_escape_string( $value->subtitle); } else {$subtitle = '';}
        if(isset($value->description)) {$description=mysql_real_escape_string( $value->description);} else {$description = '';}
        if(isset($value->originalAirDate)) {$originalAirDate = $value->originalAirDate;} else {$originalAirDate = '';}
        if(isset($value->series)) {$series = $value->series;} else {$series = '';}
        if(isset($value->showType)) {$showType = $value->showType;} else {$showType = '';}
        if(isset($value->colorCode)) {$colorCode = $value->colorCode;} else {$colorCode = '';}
        if(isset($value->syndicatedEpisodeNumber)) {$syndicatedEpisodeNumber = $value->syndicatedEpisodeNumber;} else {$syndicatedEpisodeNumber = '';}

        $sql  = "insert ignore into programs set `id` = '{$value->id}', `title` = '" . $title . "', `subtitle` = '" . $subtitle;
        $sql .= "', `description` = '" . $description . "', `showType` = '" . $showType . "', `colorCode` = '" . $colorCode . "', `series` = '";
        $sql .= $series . "', `syndicatedEpisodeNumber` = '" . $syndicatedEpisodeNumber . "', `originalAirDate` = '$originalAirDate'";
	    $this->insert_data($sql);
      }

      // assume only 1 listing!
      $value = $data->xtvd->lineups->lineup;
      // assumes multiple listings
//      foreach ($data->xtvd->lineups->lineup as $i => $value) {
	     // [id] => PC:78681
         // [name] => Local Broadcast Listings
         // [location] => Antenna
         // [type] => LocalBroadcast
         // [postalCode] => 78681    
//	    echo "Lineups:  Got {$value->id} on {$value->location} for {$value->name}\n";
//	    echo "Got a map array of objects!\n";
//	    print_r($value->map);
	
        foreach ($value->map as $i => $value2) {
	      if (isset($value2->channelMinor)) { $channelMinor = $value2->channelMinor; } else { $channelMinor = 0; }
	      $sql =  "insert ignore into lineups set `station_id` = '{$value2->station}', `channel` = '{$value2->channel}', ";
	      $sql .= "`channelMinor` = '$channelMinor'";
	
		  $this->insert_data($sql);
        }
//      }

      //Delete stuff from DB that is not a viable channel
//      $sql='delete from schedules using schedules left join chanscan on schedules.station = chanscan.xmlid where xmlid IS NULL';
//      mysql_query($sql) or die(mysql_error());

      //Delete old stuff from DB
//      $sql='delete from schedules where end_time < date_sub(now(), interval 5 day)';
//      mysql_query($sql) or die(mysql_error());

//      $sql='delete from programs using programs left join schedules on programs.id = schedules.program where program IS NULL';
//      mysql_query($sql) or die(mysql_error());

	    return true;
    }
    public function fetch($days_out=0) {
      // This function fetches schedules direct data and passes it back
      if ($days_out > 0) {
        $start=gmdate("Y-m-d\T04:59:59\Z",time()+3600*24*($days_out));
        $stop =gmdate("Y-m-d\T05:00:00\Z",time()+3600*24*($days_out+1));
       } else {
        $start=gmdate("Y-m-d\T04:59:59\Z",time());
        $stop =gmdate("Y-m-d\T05:00:00\Z",time()+3600*24);
      }
      $client = new SoapClient($this->data_url, array('exceptions' => 0,
                                                      'user_agent' => "php/".$_SERVER,
                                                      'login'      => strtolower($this->login),
                                                      'password'   => $this->password));
      $data = $client->download($start,$stop);
//print_r($data);
      return $data;
    }
    public function test_insert_data($sql) {
	  $result = $this->db->execute($sql);
	  return $result;
    }


    private function insert_data($sql) {
	  // This functions uses the DBH passed in during object construction
	  $db     = $this->db;
	  $result = $db->execute($sql);
	  return $result;
	}
	// end of class data
  }


?>