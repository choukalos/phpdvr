<?php
  
  class database {
	
	  private $dbh;
	  private $user;
	  private $password;
	  private $host;
	  private $database;
	
	  function __construct($user, $password, $host, $database = null ) {
	    $this->user = $user;
	    $this->password = $password;
	    $this->host     = $host;
	    $this->database = $database;
	    $this->dbh      = $this->connect($database);
	    return $this;
     }
	
	  public function query($sql) {
	    try {
		  $result = $this->dbh->query($sql);
	    } 
	    catch(PDOException $e) { echo $e->getMessage(); }	
	    return $result;
	  }

	  public function fetch_all($sql) {
	    try {
		  $sth  = $this->dbh->prepare($sql);
		  $sth->execute();
		  $data = $sth->fetchAll();
	    }
	    catch(PDOException $e) { echo $e->getMessage(); }
	    return $data;	
	  }  

	  public function execute($sql) {	
		try {
		 $result = $this->dbh->exec($sql);
	    } 
	    catch(PDOException $e) { echo $e->getMessage(); }	
	    return $result;
	  }
	  // ------------ Private Functions -----------
	  private function connect($database) {
		$host     = $this->host;
		$user     = $this->user;
		$password = $this->password;
		$database = $this->database;
	    try { 
		  if (is_null($database)) {
			$dbh = new PDO("mysql:host=$host",$user,$password); 			
		  } else {
			$dbh = new PDO("mysql:host=$host;dbname=$database",$user,$password); 
		  }
	    }
	    catch (PDOException $e) { echo $e->getMessage(); }
	    return $dbh; 
      }
      // ------------ End of Class
  }

?>