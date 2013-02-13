<?php

define ("DB", "pwdatabase1");
define ("USER", "root");
define ("PASS", "");

class User {
	public $id;
	public $firstName;
	public $lastName;
	public $email;
	
	//construieste un obiect User (inregistrare), pe baza id-ului
	public function __construct ($id = false) {
		
		if (!$id)
			return;
			
		$db = new mysqli('localhost', USER, PASS, DB);
		$this->id = $id;
				
		$query = "SELECT * from users where id = $id ";
		$result = $db->query($query);
		$row = $result->fetch_assoc();
		
		$this->firstName = $row['firstName'];
		$this->lastName = $row['lastName'];
		$this->email = $row['email'];
		
		$db->close();
	}
	//intoarce un utilizator, pe baza unui vector asociativ ce contine proprietatile acestuia
	public static function getUser($row)
	{
		$user = new User();
		
		$user->id = $row['id'];
		$user->firstName = $row['firstName'];
		$user->lastName = $row['lastName'];
		$user->email = $row['email'];
		
		return $user;
	}

	function __toString() 
	{
		if (!$this->id)
			return "[]";
			
		return "[".$this->id.",".$this->firstName.",".$this->lastName.",".$this->email."]<br>";
	}
	
	
}
?>