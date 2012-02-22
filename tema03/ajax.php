<?php
	$arr = array("cv_1" => "value_1", "cv_2" => "value_2", "cv_3" => "value_3");
	
	if (isset($_GET['data']) && array_key_exists($_GET['data'], $arr)) {
		echo json_encode(array('value' => $arr[$_GET['data']]));
    } else {
		echo json_encode(array("key1" => "val1", "key2" => "val2", "key3" => "val3"));
    }
?>
