<?php
  require_once(dirname(__FILE__) . '/../config.php');
  $scanfile = dirname(__FILE__)  . '/../logs/scan0.log';

  $DB     = new database($DB_USER, $DB_PASS, $DB_HOST, $DB_NAME );
  $sql    = "delete from channels where device = 'ff'";
  $result = $DB->execute($sql);
  echo "Cleaning up old tests - effected $result rows\n";


  $sql    = "insert into channels set device = 'ff', tuner= 666, channel=4";
  $result = $DB->execute($sql);
  echo "Inserted $result rows into channels\n";

  $sql    = "select * from channel where device = 'ff' ";
  $result = $DB->query($sql);
#  $result = $DB->fetch_all($sql);
  echo "Caught:  \n";
  print_r($result);
  echo " .... by row ... \n";
  foreach ($result as $row) {
    $out = join(":", $row);
    echo "$out\n";	
    echo " -- " . $row['device'] . "\n";
  }


?>