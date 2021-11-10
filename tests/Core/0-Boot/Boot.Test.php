<?php

	use PHPUnit\Framework\TestCase;

	class Boot extends TestCase{

		public function testLaunchFramework(){

			//Include the boot file
			require_once dirname(__FILE__).'/../../../dist/twist/Classes/Framework.class.php';

			//Launch the framework ready for use
			\Twist\Classes\Framework::boot();

			$this->assertTrue(defined('TWIST_LAUNCHED'));
		}
	}