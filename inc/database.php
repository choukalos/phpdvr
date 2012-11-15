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
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);	
	    return $this;
     }
	
	  public function query($sql) {
	    try {
		  $db = $this->dbh;
		  $result = $db->query($sql);
		  return $result;
	    } 
	    catch(PDOException $e) { echo $e->getMessage(); }	
	  }

	  public function fetch_all($sql) {
	    try {
		  $db = $this->dbh;
		  $sth  = $db->prepare($sql);
		  $sth->execute();
		  $data = $sth->fetchAll();
		  return $data;
	    }
	    catch(PDOException $e) { echo $e->getMessage(); }	
	  }  

	  public function execute($sql) {
		try {
		 $db = $this->dbh;		
		 $result = $db->exec($sql);
		 return $result;
	    } 
	    catch(PDOException $e) { echo $e->getMessage(); }	
	  }
	  public function close() {
	    $this->dbh = null;
	    return;	
	  }
	  public function change_database($database) {
		$this->dbh = null;
		$dbh = $this->connect($database);
		$this->dbh = $dbh;
		return $this;
	  }
	  public function check() {
	    echo "Its Alive!\n";	
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
			return $dbh;			
		  } else {
			$dbh = new PDO("mysql:host=$host;dbname=$database",$user,$password); 
			return $dbh;
		  }
	    }
	    catch (PDOException $e) { echo $e->getMessage(); }
      }
      // ------------ End of Class
  }

?>