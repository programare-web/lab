<?php
	if (!@$_POST['xmlData']) die();
	
	$simpleXMLObj = new SimpleXMLElement($_POST['xmlData']);
	
	if (!$simpleXMLObj) die();
	
	print '<content><definition>This is the tooltip for '. $simpleXMLObj->word .'</definition></content>';
?>