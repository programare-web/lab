<?php
	$arr = array("test" => "output");
	
	if (isset($_GET['data']) && array_key_exists($_GET['data'], $arr)) {
		echo json_encode(array('value' => $arr[$_GET['data']]));
    } else {
		echo json_encode(array("key1" => "val1", "key2" => "val2", "key3" => "val3"));
    }
?>
