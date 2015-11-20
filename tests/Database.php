<?php

require_once sprintf('%s/index.php',dirname(__FILE__));

class Database extends \PHPUnit_Framework_TestCase{

	public function testQuery(){

		$blQuery = \Twist::Database()->query("SELECT * FROM `twist_settings` LIMIT 1");

		$this -> assertEquals(true,$blQuery);
		$this -> assertEquals(1,\Twist::Database()->getNumberRows());
	}

	public function testGet(){

		$arrResult = \Twist::Database()->get('twist_settings','SITE_NAME','key');

		$this -> assertEquals('Travis CI Test',$arrResult['value']);
	}
}