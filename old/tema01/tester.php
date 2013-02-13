<?php

	include "testerLib.inc.php";
	
	require_once("interfaces.inc.php");
	
	//set exception handler
	set_error_handler("exception_error_handler");
	
	$test = false;
	if (isset ($_GET['test'])){
		$test = $_GET['test'];
	}
	
	$grade = 0;
	
	$test_1 = new TestSequence ("Test 1 - File existance Tests");
	$test_1->add( new FileExistanceTest("includes.inc.php file existence ",0,true,"includes.inc.php") );
	$test_1->add( new FileExistanceTest("constants.inc.php file existence ",0,true,"constants.inc.php") );
	
	if (!$test || $test == 'init') {
		$test_1->run();
		$grade += $test_1->getGrade();
	}	
	
	require_once("includes.inc.php");
	
	$test_2 = new TestSequence ("Test 2 - Database access tests");
	$test_2->add (new ConstantsDefinedTest("Database constants are defined",0,true) );
	$test_2->add (new DBAccessTest("Writing tests to database",0,true) );
	
	if (!$test || $test == 'init') {
		$test_2->run();
		$grade += $test_2->getGrade();
	}
	/***************** end of initial tests **************************/

	$ts_sgt = new TestSequence ("Test 3 - Singleton tests");
	$ts_sgt->add( new ClassExistanceTest("Singleton class definition", 0, true, "SingletonDB") );
	$ts_sgt->add( new ClassImplementsInterfaceTest("SingletonDB implements Singleton interface", 0, true, "SingletonDB", "Singleton") );
	
	class MysqlWarning extends Test { public function run () {$con = SingletonDB::connect(); return true;} }
	$ts_sgt->add( new NoExceptionDecorator( new MysqlWarning ("Test if connection has no MYSQL warnings", 1, true) ) );
	$ts_sgt->add( new NotInstantiableTest("Do not allow direct instantiation ", 3, false, "SingletonDB") );
	$ts_sgt->add( new GoodSingletonTest("Multiple 'connect' calls return same reference ", 3, false) );
	$ts_sgt->add( new SingletonIsConnectionTest("Test if SingletonDB::connect returns a connection ", 3, false) );
	
	if (!$test || $test == 'bonus') {
		$ts_sgt->run();
		$grade += $ts_sgt->getGrade();
	}
	/******************* end of bonus tests *************************/
	
	$test_4 = new TestSequence("Test 4 - Item tests");
	$test_4->add( new ClassExistanceTest("ItemFactory class definition", 0, true, "ItemFactory") );
	$test_4->add( new ClassImplementsInterfaceTest("ItemFactory implements IItemFactory interface", 0, true, "ItemFactory", "IItemFactory") );
	$test_4->add( new ContextNEDecorator ( new ItemFactoryTest("ItemFactory::getItem() simple call test", 3, true) ) );
	
	
	$test_4->add( new ContextEDecorator (new ReadItemTest("InvalidIndexException test",3, false, 9999, "pw_users"), "InvalidIndexException"));
	$test_4->add( new ContextEDecorator (new ReadItemTest("InvalidTableException test 1",3, false, 1, "pw_user"), "InvalidTableException"));
	$test_4->add( new ContextEDecorator (new ReadItemTest("InvalidTableException test 2",3, false, 1, ""), "InvalidTableException"));
	
	/* for
		$object = ItemFactory::getItem(2, ... );
		checks if '$object->property' equals with column value 'property' on row 2 from a given table  
	*/
	
	$test_4->add( new ContextNEDecorator (new ReadItemTest("Test if we can read properties of items ",4, false, 2, "pw_users") ) ) ;
	$test_4->add( new ContextEDecorator (new GetInvalidPropertyTest("Get value of invalid property (should throw an exception)",3, false), "NoSuchPropertyException"));
	$test_4->add( new ContextEDecorator (new SetInvalidPropertyTest("Set value invalid property (should throw an exception)",3, false), "NoSuchPropertyException"));
	$test_4->add( new ContextNEDecorator (new ModifyItemTest("Modify existent property ",4, false, 2, "pw_users") ) );
	$test_4->add( new ContextNEDecorator (new CreateItemTest("Create new item ",4, false,"pw_users",array("firstName","lastName")) ) );
	
	$itemST = new ItemStressTest("Stress test: random table, multiple accesses", 10, false);
	$test_4->add($itemST);
	$test_4->debuggingOff();
	
	/* stress test has huge debugging Output, better turned off */
	$itemST->debuggingOff();
	
	if (!$test || $test == 'item') {
		$test_4->run();
		$grade += $test_4->getGrade();
	}
	
	$test_5 = new TestSequence ("Test 5 - Collection tests");
	$test_5->add( new ClassExistanceTest("CollectionFactory class definition", 0, true, "CollectionFactory" ) );
	$test_5->add( new ClassImplementsInterfaceTest("CollectionFactory implements ICollectionFactory interface", 0, true, "CollectionFactory", "ICollectionFactory") );
	$test_5->add( new ContextNEDecorator( new CollectionFactoryTest("CollectionFactory::getCollection() simple call test", 1, true) ) );
	$test_5->add( new ContextNEDecorator( new HasNextSimpleTest("HasNext simple test", 1, false) ) );
	$test_5->add( new ContextNEDecorator( new NextSimpleTest("Next simple test (must return an object of type Item)", 1, false) ) );
	
	/* Test for an empty collection */
	$cTest_0 = new CollectionTest("Empty collection",1,false);
	$cTest_0->initParams("pw_empty",array(),array(),array(),array(),array(),null,null);
	$cTest_0->initQuery("SELECT * from pw_empty");
	$test_5->add(new ContextNEDecorator($cTest_0));
	
	/* Exception tests */
	//invalid table name:
	$exTest_1 = new CollectionTest("Testing if exception is thrown for invalid table",1,false);
	$exTest_1->initParams("pw_invalid",array(),array(),array(),array(),array(),null,null);
	
	$test_5->add(new ContextEDecorator($exTest_1,"MySQLException"));
	//invalid restrictions:
	$exTest_2 = new CollectionTest("Testing if exception is thrown for invalid restrictions",1,false);
	$exTest_2->initParams("pw_users",array("invalid" => "invalid"),array("invalid" => "invalid"),array("invalid" => "invalid"),array("invalid" => "invalid"),array("invalid" => array("invalid")),null,null);
	
	$test_5->add(new ContextEDecorator($exTest_2,"MySQLException"));
	//invalid restrictions:
	$exTest_3 = new CollectionTest("Testing if exception is thrown for invalid sorting",1,false);
	$exTest_3->initParams("pw_users",array(),array(),array(),array(),array(),"invalid","invalid");
	
	$test_5->add(new ContextEDecorator($exTest_3,"MySQLException"));
	
	
	/* simple collection test */
	$cTest_4 = new CollectionTest("Collection test 1 (simple list)",1, false);
	//$tableName, $equalPairs, $likePairs, $lessThanPairs, $greaterThanPairs, $inPairs, $orderBy, $orderType
	$cTest_4->initParams("pw_users",array(),array(),array(),array(),array(),null,null);
	$cTest_4->initQuery("SELECT * from pw_users ");
	$test_5->add(new ContextNEDecorator($cTest_4));
	
	/* equal test */
	$cTest_5 = new CollectionTest("Collection test 2 (some restrictions)",4, false);
	$cTest_5->initParams("pw_users",array("lastName" => "Popovici"), //$equalPairs, 
									array(), //$likePairs, 
									array(), //$lessThanPairs,
									array(), //$greaterThanPairs, 
									array("id" => array("1","2")), //$inPairs, 
									null,    //$orderBy, 
									null);   //$orderType
	$cTest_5->initQuery("SELECT * from pw_users WHERE lastName = 'Popovici' AND id IN (1,2) ");

	$test_5->add(new ContextNEDecorator($cTest_5));
	
	$cTest_6 = new CollectionTest("Collection test 3 (restrictions & sorting)",4, false);
	$cTest_6->initParams("pw_products",array("productAvailability" => "Yes"), //$equalPairs, 
									array(), //$likePairs, 
									array(), //$lessThanPairs,
									array(), //$greaterThanPairs, 
									array(), //$inPairs, 
									"id",    //$orderBy, 
									"ASC");   //$orderType
	$cTest_6->initQuery("SELECT * from pw_products WHERE productAvailability = 'Yes' ORDER BY id ASC ");
	
	$test_5->add(new ContextNEDecorator($cTest_6));
	
	$cTest_7 = new CollectionTest("Collection test 4 (displaying only first page)",4, false);
	$cTest_7->initParams("pw_products",array("productAvailability" => "Yes"), //$equalPairs, 
									array(), //$likePairs, 
									array(), //$lessThanPairs,
									array(), //$greaterThanPairs, 
									array(), //$inPairs, 
									"id",    //$orderBy, 
									"ASC",   //$orderType
									3,       //items per page
									1);      //current page to display
	
	$cTest_7->initQuery("SELECT * from pw_products WHERE productAvailability = 'Yes' ORDER BY id ASC LIMIT 0,3");
	$test_5->add(new ContextNEDecorator($cTest_7));
	
	$cTest_8 = new CollectionTest("Collection test 5 (displaying only some page)",4, false);
	$cTest_8->initParams("pw_products",array("productAvailability" => "No"), //$equalPairs, 
									array(), //$likePairs, 
									array(), //$lessThanPairs,
									array(), //$greaterThanPairs, 
									array(), //$inPairs, 
									"id",    //$orderBy, 
									"ASC",   //$orderType
									4,       //items per page
									2);      //current page to display
	
	$cTest_8->initQuery("SELECT * from pw_products WHERE productAvailability = 'No' ORDER BY id ASC LIMIT 4,4");
	$test_5->add(new ContextNEDecorator($cTest_8));
	
	$cTest_9 = new CollectionTest("Collection test 6 (testing like)",4, false);
	$cTest_9->initParams("pw_users",array("lastName" => "Popovici"), //$equalPairs, 
									array("firstName" => "M"), //$likePairs, 
									array(), //$lessThanPairs,
									array(), //$greaterThanPairs, 
									array(), //$inPairs, 
									"id",    //$orderBy, 
									"ASC");   //$orderType       
									      
	
	$cTest_9->initQuery("SELECT * from pw_users WHERE lastName ='Popovici' AND firstName LIKE 'M' order by id ASC");
	$test_5->add(new ContextNEDecorator($cTest_9));
	
	$cTest_10 = new CollectionTest("Collection test 7 (testing > and <)",4, false);
	$cTest_10->initParams("pw_products",array(), //$equalPairs, 
									array(), //$likePairs, 
									array("id" => '110'), //$lessThanPairs,
									array("id" => '104'), //$greaterThanPairs, 
									array(), //$inPairs, 
									"id",    //$orderBy, 
									"ASC");   //$orderType       
									      
	
	$cTest_10->initQuery("SELECT * from pw_products WHERE id > 104 AND id < 110 order by id ASC");
	$test_5->add(new ContextNEDecorator($cTest_10));
	
	
	$cTest_11 = new CollectionTest("Collection test 8 (combined test)",4, false);
	$cTest_11->initParams("pw_products",array("productAvailability" => "Yes"), //$equalPairs, 
									array("productDescription" => "script"), //$likePairs, 
									array("id" => '110'), //$lessThanPairs,
									array("id" => '104'), //$greaterThanPairs, 
									array("id" => array("105","107"), "productName" => array("Prod_5")), //$inPairs, 
									"productName",    //$orderBy, 
									"DESC");   //$orderType       
									      
	
	$cTest_11->initQuery("SELECT * from pw_products WHERE productAvailability = 'Yes' AND productDescription LIKE 'script' AND id < 110 AND id > 104 AND id IN (105,107) AND productName IN ('Prod_5') order by productName DESC");
	$test_5->add(new ContextNEDecorator($cTest_11));
	
	$cTest_12 = new CollectionTest("Collection test 9 (combined test returning empty collection)",4, false);
	$cTest_12->initParams("pw_products",array("productAvailability" => "Yes", "productAvailability" => "No"), //$equalPairs, 
									array("productDescription" => "script"), //$likePairs, 
									array("id" => '110'), //$lessThanPairs,
									array("id" => '104'), //$greaterThanPairs, 
									array("id" => array("105","107"), "productName" => array("Prod_5")));  //$inPairs, 									      
	
	$cTest_12->initQuery("SELECT * from pw_products WHERE productAvailability = 'Yes' AND productAvailability = 'No' AND productDescription LIKE 'script' AND id < 110 AND id > 104 AND id IN (105,107) AND productName IN ('Prod_5')");
	$test_5->add(new ContextNEDecorator($cTest_12));
	
	
	if (!$test || $test == 'collection') {
		$test_5->run();
		$grade += $test_5->getGrade();
	}
	
	$test_6 = new TestSequence ("Test 6 - Script file existance");
	$test_6->add(new FileExistanceTest("file index.php exists",0,true,"index.php") );
	
	if (!$test) {
		$test_6->run();
		$grade += $test_6->getGrade();
	}
	space();
	space();
	displayGrade($grade);
	
	
	

?>
