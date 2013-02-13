<?php
	
	//transform PHP errors to exceptions; that way, we can catch them;
	function exception_error_handler($errno, $errstr, $errfile, $errline ) {
		
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	
	}

	abstract class Test {
		
		protected $testName;
		public $value;
		protected $errorOutput = null;
		protected $stops = false;
		
		protected $debug = false;
		protected $debugInfo = "";
		
		
		//members that will be populated after test is run;
		private $result = null;
		
		// runs current test
		public abstract function run ();
		// tells us if we should stop the test sequence
		
		//default basic constructor; may be overriden
		public function __construct ($name = false, $value = false, $stops = false) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
		}
		
		//runs test, and populates test environment
		public function runTest () {
			$this->result = $this->run();
		}		
		public function getStatus () {
			return ($this->result) ? ("PASSED") : ("FAILED");
		}
		public function debuggingOn(){
			
			$this->debug = true;
		}
		public function debuggingOff(){
			
			$this->debug = false;
		}
		public function getErrorOutput () {
			return $this->errorOutput;
		}
		public function getDebugInfo () {
			return $this->debugInfo;
		}
		public function getDebug () {
			return $this->debug;
		}
		public function displayResults () {
			?>
			<div style="width:800px; text-align:left; float:left"> <?php echo $this->testName; ?> (<?php echo $this->value; ?>p) </div>
			<div style="width:200px; text-align:left; float:left; font-weight:bold;"> <?php echo $this->getStatus(); ?> </div>
			<div style="clear:both"></div>
			<?php
			
			if ($this->debug) {
				
				?>
				<div style="width:800px; text-align:left; float:left; color:#66CC00;"> <?php echo "<b>Debugging output:</b></br>".$this->debugInfo; ?> </div>
				<div style="clear:both"></div>
				<?php
			}
			
			if ($this->errorOutput != false)
			{
				?>
				<div style="width:800px; text-align:left; float:left; color:#FF0000;"> <?php echo "<b>Error output:</b>".$this->errorOutput; ?> </div>
				<div style="clear:both"></div>
				<?php
			}
			
		}
		public function getResult () {
			return $this->result;
		}
		public function stops (){
			return $this->stops;
		}
		public function success () {
			return ($this->result == true);
		}
		public function failed () {
			return ($this->result == false);
		}
	}
		
	class TestSequence extends Test {
	
		public $tests = array();
		private $grade = 0;
		
		public function __construct ($testName) {
			$this->testName = $testName;
		}
		//@Override
		public function debuggingOn(){
			foreach ($this->tests as $test) {
				$test->debuggingOn();
			}
		}
		public function run (){
			
			
			$this->displayTitle();
			
			foreach ($this->tests as $test){
				
				$test->runTest();
				//display Test Results
				$test->displayResults();
				
				if ($test->success())
					$this->grade += $test->value;
				
				//stop if test failed and it is a stopper	
				if ($test->failed() && $test->stops()){
					$this->displayGrade();
					return;
				}
			}
			$this->displayGrade();
		}
		public function add (Test $test) {
			$this->tests [] = $test;
		}
		private function displayGrade() {
			?>
			<div style="width:800px; text-align:left; font-weight:bold; color:#0000ff;"> <?php echo $this->testName; ?> grade: <?php echo $this->grade; ?> </div>
			<?php
		}
		private function displayTitle () {
		?>
		<br>
		<div style="width:800px; text-align:left;"><b> <?php echo $this->testName; ?> </b></div>
		<?php
		}
		public function getGrade(){
			return $this->grade;
		}
	}
	
	function space(){
		echo "<br/>";
	}
	/***************** TEST DECORATORS *******************/
	
	//defines an abstract context for running tests
	abstract class ContextDecorator extends Test {
		public $test;
		public function __construct ($test) {
			$this->testName = $test->testName;
			$this->value = $test->value;
			$this->stops = $test->stops;
			$this->test = $test;
		}
		protected abstract function contextHeader ();
		protected abstract function contextFooter ();
		public function run () {
			$this->contextHeader();
			$value = $this->test->run();
			$this->contextFooter();
			
			//var_dump($this->test->getDebugInfo());
			$this->errorOutput = $this->test->getErrorOutput();
			$this->debugInfo .= $this->test->getDebugInfo();
			$this->debug = $this->debug || $this->test->getDebug();
			return $value;
		}
	}
	
	//decorator that runs a test, and returns false if test throws exception
	class NoExceptionDecorator extends Test {
		public $test;
		public function __construct ($test) {
			$this->testName = $test->testName;
			$this->value = $test->value;
			$this->stops = $test->stops;
			$this->test = $test;
		}
		public function run () {
			try {
				
				$value = $this->test->run();

			}catch (Exception $e) {
				$this->errorOutput = "Tester caught unexpected exception:<br/> $e <br/>";
				$this->debugInfo .= $this->test->getDebugInfo();
				$this->debug = $this->debug || $this->test->getDebug();
				return false;
			}
			$this->errorOutput = $this->test->getErrorOutput();
			$this->debugInfo .= $this->test->getDebugInfo();
			$this->debug = $this->debug || $this->test->getDebug();
			return $value;
		}
	}
	
	class ExceptionDecorator extends Test {
		public $test;
		public $exception;
		
		public function __construct ($test, $exception) {
			$this->testName = $test->testName;
			$this->value = $test->value;
			$this->stops = $test->stops;
			$this->test = $test;
			$this->exception = $exception;
		}
		public function run () {
			try {
				$this->test->run();
			}
			catch (Exception $e) {
				if (get_class($e) == $this->exception) {
					
					$this->debugInfo .= $this->test->getDebugInfo();
					$this->debug = $this->debug || $this->test->getDebug();
					
					return true;
				}
				else 
					$this->errorOutput = "Received exception $e, expected ".$this->exception." <br/>";
			}
			if (!$this->errorOutput)
				$this->errorOutput = "Test should throw exception ".$this->exception."; No exception is thrown ";
			
			$this->errorOutput .= $this->test->getErrorOutput();
			$this->debugInfo .= $this->test->getDebugInfo();
			$this->debug = $this->debug || $this->test->getDebug();
			
			return false;
		}
	}
	/* ********************* MIXED DECORATORS *******************/
	// Context and NoException decorator combination
	class ContextNEDecorator extends Test {
		public $test;
		public function __construct ($test) {
			$this->testName = $test->testName;
			$this->value = $test->value;
			$this->stops = $test->stops;
			$this->test = $test;
		}
		public function run () {
		
			$decoratedTest = new DBContextDecorator(new NoExceptionDecorator($this->test));
			$value = $decoratedTest->run();
			$this->errorOutput = $decoratedTest->getErrorOutput();
			$this->debugInfo = $decoratedTest->getDebugInfo();
			$this->debug = $this->debug || $decoratedTest->getDebug();
			
			return $value;
		}
	}
	// Context and Exception decorator combination
	class ContextEDecorator extends Test {
		public $test;
		public $exception;
		
		public function __construct ($test, $exception) {
			$this->testName = $test->testName;
			$this->value = $test->value;
			$this->stops = $test->stops;
			$this->test = $test;
			$this->exception = $exception;
		}
		public function run () {
		
			$decoratedTest = new DBContextDecorator(new ExceptionDecorator($this->test, $this->exception));

			$value = $decoratedTest->run();
			//var_dump($decoratedTest->getDebugInfo());
			
			$this->errorOutput = $decoratedTest->getErrorOutput();
			$this->debugInfo .= $decoratedTest->getDebugInfo();
			$this->debug = $this->debug || $decoratedTest->getDebug();
			return $value;
		}
	}
	
	
	/**************** TEST IMPLEMENTATIONS ************************/
	/* file existance test */
	class FileExistanceTest extends Test {
		public $fileName;
		public function __construct ($name, $value, $stops, $fileName) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->fileName = $fileName;
		}
		public function run () {
			$this->debugInfo .= "Checking if file ".$this->fileName." exists ";
			$value = file_exists($this->fileName);
			if (!$value) {
				$this->errorOutput = "No such file ".$this->fileName." exists ";
			}
			return $value; 
		}
	}
	/* class existance test */
	class ClassExistanceTest extends Test {
		public $className;
		public function __construct ($name, $value, $stops, $className) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->className = $className;
		}
		public function run () {
			$this->debugInfo .= "Checking if class ".$this->className." exists <br/>";
			$value = class_exists($this->className);
			if (!$value) {
				$this->errorOutput = "No such class ".$this->className." exists ";
			}
			return $value;
		}
	}
	/* class implements interface test */
	class ClassImplementsInterfaceTest extends Test {
		public $className;
		public $interfaceName;
		public function __construct ($name, $value, $stops, $className,$interfaceName) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->className = $className;
			$this->interfaceName = $interfaceName;
		}
		public function run () {
			$this->debugInfo .= "Checking if class ".$this->className." implements interface ".$this->interfaceName." <br/>";
			$reflectionClass = new ReflectionClass($this->className);
			$value = $reflectionClass->implementsInterface($this->interfaceName);
			if (!$value) {
				$this->errorOutput = "Class ".$this->className." does not implement interface ".$this->interfaceName." ";
			}
			return $value;
		}
	}
	
	//concrete decorator; context is a database created for tests
	class DBContextDecorator extends ContextDecorator {
		protected function contextHeader (){
			createDBEnvironment();
		}
		protected function contextFooter (){
			deleteDBEnvironment();
		}
	}
	
	/*************** PARTICULAR TESTS *************************/
	class ItemStressTest extends Test {
		public $itemNo = 100;
		public $colNo = 6;
		
		public function run () {
			$mysqli = new mysqli (ADDRESS, USERNAME, PASSWORD, DATABASE);
			
			$table = "pw_table_".substr(md5(rand(5,1000)),0,10);
			$this->debugInfo .= "Build random table name: $table;<br/>";
			
			$columns = array ();
			$i = $this->colNo;
			while ($i>0) {
				$col = "col_".substr(md5(rand(5,1000)),0,10);
				$columns [] = $col;
				$this->debugInfo .= "Build random column name: $col;<br/>";
				$i--;
			}
			$columns = array_unique($columns); // if two columns have the same name, remove duplicates
			$this->colNo = count($columns); // and alter the number of columns
			
			$query = "CREATE TABLE IF NOT EXISTS `$table` (`id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT, ";
			foreach ($columns as $col){
				$query .= "`$col` varchar(45) NOT NULL,";
			}
			$query = substr($query,0,-1);
			$query .= ")";
			$this->debugInfo .= "Create query: $query;<br/>";
			$result = $mysqli->query($query);
			$this->debugInfo .= "Run query ...<br/>";
			if (!$result)
			{
				$this->errorOutput = "Running query failed;<br/>";
				return false;
			}
			
			/* testing insert */
			for ($i=0; $i < $this->itemNo; $i++) {
				$ti = new CreateItemTest("",0,true,$table,$columns);
				if (!$ti->run()) {
					$this->errorOutput = $ti->getErrorOutput();
					$mysqli->query("DROP table $table");
					return false;
				}
				else {
					$this->debugInfo .= $ti->getDebugInfo();
				}
			}
			/* testing modify */
			
			$this->debugInfo .= "Build a list of ids from table $table <br/>";
			$ids = array();
			$result = $mysqli->query ("SELECT id from $table");
			while ($row = $result->fetch_assoc()){
				$ids [] = $row["id"];
			}
			foreach ($ids as $id) {
				$ti = new ModifyItemTest ("",0,true,$id,$table);
				if (!$ti->run()) {
					$this->errorOutput = $ti->getErrorOutput();
					$mysqli->query("DROP table $table");
					return false;
				}
				else {
					$this->debugInfo .= $ti->getDebugInfo();
				}
			}
			
			/* testing select */
			$this->debugInfo .= "Read properties from items in $table <br/>";
			foreach ($ids as $id) {
				$ti = new ReadItemTest ("",0,true,$id,$table);
				if (!$ti->run()) {
					$this->errorOutput = $ti->getErrorOutput();
					$mysqli->query("DROP table $table");
					return false;
				}
				else {
					$this->debugInfo .= $ti->getDebugInfo();
				}
			}
			
			$mysqli->query("DROP TABLE $table");
			return true;
		}
	}
	
	/* Unordered collection test */
	class CollectionTest extends Test {
		private $tableName;
		private $equalPairs;
		private $likePairs;
		private $lessThanPairs;
		private $greaterThanPairs;
		private $inPairs;
		private $orderBy;
		private $orderType;
		
		private $query;
		private $table;
		private $itemsPerPage = 0;
		private $pageNo = 0;
		public function initQuery ($query) {
			$this->query = $query;
		}
		
		public function initParams ($tableName, $equalPairs, $likePairs, $lessThanPairs, $greaterThanPairs, $inPairs, $orderBy = false, $orderType = false, $itemsPerPage = 0, $pageNo = 0) {
			$this->table = $tableName;
			$this->equalPairs = $equalPairs;
			$this->likePairs = $likePairs;
			$this->lessThanPairs = $lessThanPairs;
			$this->greaterThanPairs = $greaterThanPairs;
			$this->inPairs = $inPairs;
			$this->orderBy = $orderBy;
			$this->orderType = $orderType;
			
			$this->itemsPerPage = $itemsPerPage;
			$this->pageNo = $pageNo;
		}
		
		public function run () {

			$this->debugInfo .= "Creating collection ".$this->table."<br/>";
			
			$collection = CollectionFactory::getCollection ($this->table, 
															$this->equalPairs, 
															$this->likePairs, 
															$this->lessThanPairs, 
															$this->greaterThanPairs, 
															$this->inPairs, 
															$this->orderBy, 
															$this->orderType,
															$this->itemsPerPage,
															$this->pageNo);
			
			$con = new mysqli(ADDRESS, USERNAME, PASSWORD, DATABASE);
			$result = $con->query($this->query);
			
			$counter = 0;
			$resultSet = array ();
			$itemSet = array ();
			$this->debugInfo .= "Fetching selected table from MySQL:<br/>";
			while ($row = $result->fetch_assoc()) {
				$resultSet [$row['id']] = $row['id'];
				$this->debugInfo .= formatRow($row);
			}			
			$con->close();
			
			$this->debugInfo .= "Iterating over collection:<br/>";
			
			/* check elements in the colllection */
			while ($collection->hasNext()) {
				$item = $collection->next();
				$itemSet [] = $item;
				$this->debugInfo .= "Current item id:".$item->getId()."<br/>";
				
				//collection has too many items
				if ( ($this->orderBy == null && $this->orderType == null && empty($resultSet)) || ($this->orderBy && $this->orderType && !current($resultSet)) ){
					$this->errorOutput = "In your collection there are more items than expected <br/>";
					//$this->debugInfo .= "In your collection there are more items than expected <br/>";
					return false;
				}
				
				//unordered list
				if ($this->orderBy == null && $this->orderType == null) {
					//collection has items that should not be there
					if (!in_array($item->getId(), $resultSet)) {
						$this->errorOutput = "Your collection has (at least one) item (Id: ".$item->getId().") that does not belong in the collection <br/>";
						//$this->debugInfo .= "Your collection has (at least one) item (Id: ".$item->id.") that does not belong in the collection <br/>";
						return false;
					}
					else {
						unset($resultSet[$item->getId()]);
					}
				}
				//ordered list
				else{
					$itemId = current($resultSet);
					if ($item->getId() != $itemId) {
						$this->errorOutput = "Item with id ".$item->getId()." is not in proper order <br/>";
						return false;
					}
					else {
						next($resultSet);
					}
					
				}
				$counter ++;
				if ($counter > 10000) {
					$this->errorOutput = "Possible infinite loop when iterating over collection <br/>";
					//$this->debugInfo .= "Possible infinite loop when iterating over collection <br/>";
					return false;
				}
			}
			//collection lasks certain items
			if ( ($this->orderBy == null && $this->orderType == null && !empty($resultSet)) || ($this->orderBy && $this->orderType && current($resultSet)) ) {
				$this->errorOutput = "Your collection lacks several items <br/>";
				return false;
			}
			$this->debugInfo .= "Collection has proper number of elements </br>";
			if ($this->orderBy && $this->orderType) 
				$this->debugInfo .= "Collection has proper order <br/>";
			
			$this->debugInfo .= "Checking if retrieved elements from collection are valid (the database actually contains all info)</br>";
			//check validity of received items
			foreach ($itemSet as $item) {
				$con = new mysqli(ADDRESS, USERNAME, PASSWORD, DATABASE);
				$this->debugInfo .= "Checking item with id = ".$item->getId()."<br/>";
				$res = $con->query("SELECT * from ".$this->table." where id=".$item->getId(). " ");
				$row = $res->fetch_assoc();
				$con->close();
				
				foreach ($row as $key => $value) {
					if ($item->$key != $value)
					{
						$this->errorOutput = " Item with id = ".$item->getId().", at key $key has an invalid value ".$item->$key."; correct is $value <br/>";
						//$this->debugInfo .= " Item with id = ".$item->getId().", at key $key has an invalid value ".$item->$key."; correct is $value <br/>";
						return false;
					}
				}
			}
			$this->debugInfo .= "Current test finished <br/><br/>";
			
			
			return true;	
		}
	}
	
	class NextSimpleTest extends Test {
		public function run () {
			$this->debugInfo .= "Try to build a simple collection with no restrictions <br/>";
			$col = CollectionFactory::getCollection("pw_users",array(),array(),array(),array(),array(), null, null);
			$this->debugInfo .= "Call next() method on that collection <br/>";
			$item = $col->next();
			
			if (!$item)
			{
				$this->errorOutput = " next() method returns false "; 
				return false;
			}
			if ($item instanceof IItem)
			{
				$this->debugInfo .= " next() correctly returns an object of type IItem ";
				return true;
			}
			$this->errorOutput = " next() does not return an object of type IItem ";
			return false;
		}
	}
	
	class HasNextSimpleTest extends Test {
		public function run () {
			$this->debugInfo .= "Create a simple collection (that contains elements) and call hasNext(); should return true; </br>";
			$col = CollectionFactory::getCollection("pw_users",array(),array(),array(),array(),array(), null, null);
			$value = $col->hasNext();
			if (!$value) {
				$this->errorOutput = "Method hasNext() incorrectly returns false ";
			}
			return $value;
		}
	}
	
	/* create a new record */
	class CreateItemTest extends Test {
		
		/*
		$table - name of the table where data will be added
		$columns - array containing column names
		*/
		public function __construct ($name, $value, $stops, $table, $columns) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->table = $table;
			$this->columns = $columns;
		}
		
		public function run () {
			
			/* generate random values */
			$values = array();
			$valNo = count($this->columns);
			while ($valNo > 0){
				$values [] = "val_".substr(md5(rand(5,1000)),0,10);
				$valNo--;
			}
			
			/* build Item input data */
			$array = array_combine($this->columns,$values);
			
			$this->debugInfo .= "Create an empty item <br/>";
			$obj = ItemFactory::getItem(false,$this->table);
			
			$this->debugInfo .= "Populate item with values <br/>";
			
			$obj->populate($array);
			
			$con = new mysqli(ADDRESS, USERNAME, PASSWORD, DATABASE);
			$res = $con->query("SELECT * from ".$this->table." where id=".$obj->getId(). " ");
			$row = $res->fetch_assoc();
			$con->close();
			
			$this->debugInfo .= "Test if object with id = ".$obj->getId()." has correct properties: <br/>";
			foreach ($row as $key => $value) {
				$this->debugInfo .= "Checking if $key = $value <br/>";
				if ($obj->$key != $value)
				{
					$this->errorOutput = "Object with id = ".$obj->getId()." has $key != $value (which should be the correct value)";
					return false;
				}
			}
			return true;
		}
	}
	
	/* checks if item members are table row properties */
	class ModifyItemTest extends Test {
		public $table;
		public $id;
		public function __construct ($name, $value, $stops, $id, $table) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->table = $table;
			$this->id = $id;
		}
		public function run () {
			
			$obj = ItemFactory::getItem($this->id,$this->table);
			
			$con = new mysqli(ADDRESS, USERNAME, PASSWORD, DATABASE);
			$res = $con->query("SELECT * from ".$this->table." where id=".$this->id. " ");
			$row = $res->fetch_assoc();
			//echo "here";
			$old_values = array ();
			$tok = rand(5, 100);
			
			$this->debugInfo .= "Generate modified values: <br/>";
			
			//update fields
			foreach ($row as $key => $value) {
				//skip id
				if ($key == "id")
					continue;
				$obj->$key = $tok.$value;
				$old_values[$key] = $value;
				$this->debugInfo .= " $key was $value; will be modified to ".$tok.$value." <br/>";
			}
			//check if they are updated in DB
			$res = $con->query("SELECT * from ".$this->table." where id=".$this->id. " ");
			$row = $res->fetch_assoc();
			$con->close();
			
			$this->debugInfo .= "Checking if modifications are reflected in database </br>";
			foreach ($row as $key => $value) {
				if ($key == "id")
					continue;
				
				if ($value != $tok.$old_values[$key])
				{
					$this->errorOutput = "Value $value is different from ".$tok.$old_values[$key]."; modification failed for record ".$this->id." in table ".$this->table."";
					return false;
				}
			}
			
			return true;
		}
	}
	
	class ReadItemTest extends Test {
		public $table;
		public $id;
		public function __construct ($name, $value, $stops, $id, $table) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->table = $table;
			$this->id = $id;
		}
		public function run () {
			
			$this->debugInfo .= "Try to get item with id = ".$this->id." <br/>";
			$obj = ItemFactory::getItem($this->id, $this->table);
			$con = new mysqli(ADDRESS, USERNAME, PASSWORD, DATABASE);

			$res = $con->query("SELECT * from ".$this->table." where id=".$this->id. " ");
			$row = $res->fetch_assoc();
			$con->close();			
			
			foreach ($row as $key => $value) {
				$this->debugInfo .= "Checking if object has property $key = $value <br/>";
				if ($obj->$key != $value)
				{
					$this->errorOutput = " Object with id = ".$this->id." has $key != $value (which is the correct one)";
					return false;
				}
			}
			return true;
		}
	}
	
	class GetInvalidPropertyTest extends Test {
		public function run () {
			$this->debugInfo .= "Retrieve item with id = 3; <br/>";
			$obj = ItemFactory::getItem(3, "pw_users");
			$tok = md5(rand (5, 150));
			$this->debugInfo .= "Try to get inexistent property $tok; should throw NoSuchPropertyException <br/>";
			$value = $obj->$tok;
		}
	}
	
	class SetInvalidPropertyTest extends Test {
		public function run () {
			$this->debugInfo .= "Retrieve item with id = 3; <br/>";
			$obj = ItemFactory::getItem(3, "pw_users");
			$tok = md5(rand (5, 150));
			$this->debugInfo .= "Try to set inexistent property $tok; should throw NoSuchPropertyException <br/>";
			$obj->$tok = "new value";
		}
	}
	
	class ItemFactoryTest extends Test {
		public function run () {
			$this->debugInfo .= "Use ItemFactory::getItem to get item of id = 1 from table pw_users <br/>";
			$ob = ItemFactory::getItem(1,"pw_users");
			if (!$ob) {
				$this->errorOutput = "ItemFactory::getItem() returns false ";
				return false;
			}
			if ($ob instanceof IItem)
				return true;
			
			$this->errorOutput = "ItemFactory::getItem() does not return an object of type IItem ";
			return false;
		}
	}
	
	class CollectionFactoryTest extends Test {
		public function run () {
			$this->debugInfo .= "Use CollectionFactory::getCollection to get collection (table) pw_users <br/>";
			$ob = CollectionFactory::getCollection("pw_users",array(),array(),array(),array(),array(), null, null);
			if (!$ob) {
				$this->errorOutput = "CollectionFactory::getCollection() returns false ";
				return false;
			}
			if ($ob instanceof ICollection)
				return true;
			
			$this->errorOutput = "CollectionFactory::getCollection() does not return an object of type ICollection ";
			return false;
		}
	}
	
	class NotInstantiableTest extends Test {
		public $className;
		public function __construct ($name, $value, $stops, $className) {
			$this->testName = $name;
			$this->value = $value;
			$this->stops = $stops;
			$this->className = $className;
		}
		public function run () {
			$this->debugInfo .= "Try to instantiate class ".$this->className."; Should not be possible <br/>";
			$reflectionClass = new ReflectionClass($this->className);
			$value = !$reflectionClass->IsInstantiable();
			if (!$value) {
				$this->errorOutput = "Class ".$this->className." can be directly instantiated ";
			}
			return $value;
		}
	}
	
	class GoodSingletonTest extends Test {
		public function run () {
			$inst1 = SingletonDB::connect();
			$inst2 = SingletonDB::connect();
			return $inst1 === $inst2;
		}
	}
	
	class SingletonIsConnectionTest extends Test {
		public function run () {
		
			$con = SingletonDB::connect();
			if (get_class($con) == 'mysqli' && !mysqli_connect_errno())
				return true;
			
			if (is_resource($con) && get_resource_type($con) == "mysql link")
				return false;
		}
	}
	
	class ConstantsDefinedTest extends Test {
		
		public function run () {
			
			$errorOutput = "";
			if (!defined('ADDRESS'))
				$errorOutput .= "ADDRESS ";
			if (!defined('DATABASE'))
				$errorOutput .= "DATABASE ";
			if (!defined('USERNAME'))
				$errorOutput .= "USERNAME ";
			if (!defined('PASSWORD'))
				$errorOutput .= "PASSWORD ";

			if ($errorOutput == "")
				return true;
			else
			{
				$errorOutput .=" constant(s) is(are) not defined ";
				$this->errorOutput = $errorOutput;
				return false;
			}
		}
	}
	
	class DBAccessTest extends Test {
	
		public function run () {
		
			try {
				$mysqli = new mysqli (ADDRESS, USERNAME, PASSWORD, DATABASE);
				// if connection failed, stop
									
				$queryArray = createDBEnvironmentQuery(); 
				
				foreach ($queryArray as $query){
					$result = $mysqli->query($query);
					if (!$result) {
						$this->errorOutput = $query." produces ".$mysqli->error;
						$mysqli->close();
						break;
					}
				}
				if ($this->errorOutput)
					return false;
				
				$queryArray = deleteDBEnvironmentQuery();
				foreach ($queryArray as $query){
					$mysqli->query($query);
				}
				$mysqli->close();
				return true;
				
			} catch (ErrorException $e) {
				return false;
			}
		}
	}
	
	
	/************* SUPPORT FUNCTIONS ******************/
	function formatRow ($row){
		$display = "[";
		foreach ($row as $key => $value)
			$display .= "$key:$value, ";
		$display = substr ($display,0, -2);
		$display .= "]</br>";
		return $display;
	}
	
	function createDBEnvironment() {
		$mysqli = new mysqli (ADDRESS, USERNAME, PASSWORD, DATABASE);
		$queryArray = createDBEnvironmentQuery(); 
		foreach ($queryArray as $query){
			$mysqli->query($query);
		}
		$mysqli->close();
	}
	
	
	function deleteDBEnvironment() {
		$mysqli = new mysqli (ADDRESS, USERNAME, PASSWORD, DATABASE);
		$queryArray = deleteDBEnvironmentQuery(); 
		foreach ($queryArray as $query){
			$mysqli->query($query);
		}
		$mysqli->close();
	}
	
	function createDBEnvironmentQuery () {
	
		$queryArray[] = "
		CREATE TABLE IF NOT EXISTS `pw_users` (
		  `id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
		  `firstName` varchar(45) NOT NULL,
		  `lastName` varchar(45) NOT NULL
		)
		";
		
		$queryArray[] = "
		CREATE TABLE IF NOT EXISTS `pw_products` (
		  `id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
		  `productName` varchar(45) NOT NULL,
		  `productDescription` varchar(45) NOT NULL,
		  `productAvailability` varchar(45) NOT NULL
		)
		";
		$queryArray[] = "
		CREATE TABLE IF NOT EXISTS `pw_empty` (
		  `id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
		  `col1` varchar(45) NOT NULL,
		  `col2` varchar(45) NOT NULL,
		  `col3` varchar(45) NOT NULL
		)
		";
		
		$queryArray [] = "INSERT INTO `pw_users` (`id`, `firstName`, `lastName`) VALUES (1, 'Matei', 'Popovici');";
		$queryArray [] = "INSERT INTO `pw_users` (`id`, `firstName`, `lastName`) VALUES (2, 'Andrei', 'Popovici');";
		$queryArray [] = "INSERT INTO `pw_users` (`id`, `firstName`, `lastName`) VALUES (3, 'Mihnea', 'Popovici');";
		$queryArray [] = "INSERT INTO `pw_users` (`id`, `firstName`, `lastName`) VALUES (4, 'Geo', 'Geo');";
		$queryArray [] = "INSERT INTO `pw_users` (`id`, `firstName`, `lastName`) VALUES (5, 'Traian', 'Traian');";
		
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (100, 'MyProduct', 'MyDesc', 'Yes');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (101, 'OtherProduct', 'OtherDesc', 'No');";
		
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (102, 'Prod_2', 'Description_2', 'Yes');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (103, 'Prod_3', 'Description_3', 'No');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (104, 'Prod_4', 'Description_4', 'No');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (105, 'Prod_5', 'Description_5', 'Yes');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (106, 'Prod_6', 'Description_6', 'Yes');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (107, 'Prod_7', 'Description_7', 'Yes');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (108, 'Prod_8', 'Description_8', 'No');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (109, 'Prod_9', 'Description_9', 'No');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (110, 'Prod_10', 'Description_10', 'No');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (111, 'Prod_11', 'Description_11', 'Yes');";
		$queryArray [] = "INSERT INTO `pw_products` (`id`, `productName`, `productDescription`, `productAvailability`) VALUES (112, 'Prod_12', 'Description_12', 'Yes');";
		
		
		
		
		return $queryArray;
	}
	
	
	function deleteDBEnvironmentQuery () {
		$queryArray[] = "DROP TABLE `pw_users`;";
		$queryArray[] = "DROP TABLE `pw_products`;";
		$queryArray[] = "DROP TABLE `pw_empty`;";
		
		return $queryArray;
	}
	function displayGrade ($grade) {
		?>
		<div style="width:800px; text-align:left; font-weight:bold; color:#0000ff;"> Grade on tests: <?php echo $grade; ?> </div>
		<?php
	}
?>