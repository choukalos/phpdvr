<?php

  require_once(dirname(__FILE__).'/../config.php');
  // overwrite DB, assuming database has been created and tables/views setup
  $DB = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );

  $sql =  " update channels inner join all_stations on (substring_index(channels.fcc_channel,'.',1) ";
  $sql .= "= all_stations.channel and substring_index(substring_index(channels.fcc_channel,'.',-1),'.',1) ";
  $sql .= "= all_stations.channelMinor) set channels.station_id = all_stations.station_id ";
  $DB->execute($sql);

?>