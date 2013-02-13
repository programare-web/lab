<?php
	class InvalidIndexException extends Exception {
		protected $message = 'Invalid Table Index Exception';   // exception message
	}
	class InvalidTableException extends Exception {
		protected $message = 'Invalid Table Exception';
	}
	class NoSuchPropertyException extends Exception {
		protected $message = 'No Such Property for Item Exception';
	}
	class NoMoreElementsException extends Exception {
		protected $message = 'The collection contains no more elements';
	}
	class MySQLException extends Exception {
		protected $message = 'Invalid MySQL query';
	}
?>