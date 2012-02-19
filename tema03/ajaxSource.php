<?php

	$arr = array("cv_1" => "value_1", "cv_2" => "value_2", "cv_3" => "value_3");
	
	//useful for combo box selects
	if (isset($_GET['data']) && array_key_exists($_GET['data'], $arr))
		echo $arr[$_GET['data']];
	//useful for editboxes
	else
		echo "key1=val1&key2=val2&key3=val3";
		//echo $_GET['data'];

?>
