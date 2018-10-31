<?php

	use PHPUnit\Framework\TestCase;

	class Asset extends TestCase{

		public function testAdd(){
			$intAssetID = \Twist::Asset()->add('https://twistphp.com',9,'TwistPHP');
			$this -> assertEquals($intAssetID,1);
		}

		public function testGet(){
			$arrAsset = \Twist::Asset()->get(1);
			$this -> assertEquals($arrAsset['data'],'https://twistphp.com');
		}
	}