<?php
interface Singleton {
	public static function connect ();
}
interface IItemFactory {
	public static function getItem ($id, $tableName);
}
interface IItem {
	public function getId ();
	public function __get ($value);
	public function __set ($property, $value);
	public function populate ($keyValueSet);
}
interface ICollection {
	public function getNumberOfPages ();
	public function hasNext ();
	public function next ();
}
interface ICollectionFactory {
	public static function getCollection ($tableName, $equalPairs, $likePairs, $lessThanPairs, $greaterThanPairs, $inPairs, $orderBy = false, $orderType = false, $itemsPerPage = 0, $pageNo = 0);
}
?>