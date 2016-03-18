<?php

class Hooks extends \PHPUnit_Framework_TestCase{

	public function testGetAllHooks(){
		$this->assertTrue(array_key_exists('TWIST_VIEW_TAG',\Twist::framework()->hooks()->getAll()));
	}

	public function testRegisterHook(){
		\Twist::framework()->hooks()->register('travisCI','travis-test-hook','test-hook');
		$this->assertEquals('test-hook',\Twist::framework()->hooks()->get('travisCI','travis-test-hook'));
	}

	public function testGetRegisteredHook(){
		$this->assertEquals('test-hook',\Twist::framework()->hooks()->get('travisCI','travis-test-hook'));
	}

	public function testCancelRegisteredHook(){
		\Twist::framework()->hooks()->cancel('travisCI','travis-test-hook');
		$this->assertEquals(array(),\Twist::framework()->hooks()->get('travisCI','travis-test-hook'));
	}

}