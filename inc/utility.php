<?php
  // Utility functions and helpers
  function epoctime_to_datetime($epoctime) {
	return date('Y-m-d H:i:s', $epoctime);
  }

  function db_query($dbh, $sql) {
    try {
	  $result = $dbh->query($sql);
    } 
    catch(PDOException $e) { echo $e->getMessage(); }	
    return $result;
  }

  function db_fetch_all($dbh,$sql) {
    try {
	  $sth  = $dbh->prepare($sql);
	  $sth->execute();
	  $data = $sth->fetchAll();
    }
    catch(PDOException $e) { echo $e->getMessage(); }
    return $data;	
  }  

  function db_execute($dbh, $sql) {	
	try {
	 $result = $dbh->exec($sql);
    } 
    catch(PDOException $e) { echo $e->getMessage(); }	
    return $result;
  }

  // template function
  // call function on template filename and pass variable array for generation as a return string to echo.
  function apply_template($tpl_file, $vars = array(), $include_globals = true) {
    // 
    extract($vars);
    if ($include_globals) extract($GLOBALS, EXTR_SKIP);
    // process template
    ob_start();
    require($tpl_file);
    $applied_template = ob_get_contents();
    ob_end_clean();
    // return as string
    return $applied_template;
  }






?>