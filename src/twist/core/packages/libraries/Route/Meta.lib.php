<?php
	/**
	 * This file is part of TwistPHP.
	 *
	 * TwistPHP is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * TwistPHP is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
	 * @link       http://twistphp.com
	 *
	 */

	namespace TwistPHP\Packages;

	/**
	 * Route Meta Tag manager and template extension
	 * @package TwistPHP\Packages
	 */
	class Meta{

		protected $arrTags = array();

		protected function add($strType,$strName,$strValue){
			$this->arrTags[] = array(
				'type' => $strType,
				'name' => $strType,
				'value' => $strType
			);
		}

		public function title($strValue){
			$this->add('title','title',$strValue);
		}

		public function description($strValue){
			$this->add('meta','description',$strValue);
		}

		public function keywords($strValue){
			$this->add('meta','keywords',$strValue);
		}

		public function author($strValue){
			$this->add('meta','author',$strValue);
		}

		public function charset($strValue){
			$this->add('meta','charset',$strValue);
		}

		public function language($strValue){
			$this->add('meta','language',$strValue);
		}

		public function robots($strValue){
			$this->add('meta','robots',$strValue);
		}

		public function custom($strKey,$strValue){
			$this->add('meta',$strKey,$strValue);
		}

		public function ogTitle($strContent){
			$this->add('og','title',$strContent);
		}

		public function ogType($strContent){
			$this->add('og','type',$strContent);
		}

		public function ogImage($strURL,$intWidth=null,$intHeight=null,$strType=null){
			$this->add('og','image',$strURL);

			if(!is_null($intWidth)){
				$this->add('og','image:width',$intWidth);
			}

			if(!is_null($intHeight)){
				$this->add('og','image:height',$intHeight);
			}

			if(!is_null($strType)){
				$this->add('og','image:type',$strType);
			}
		}

		public function ogURL($strContent){
			$this->add('og','url',$strContent);
		}

		public function ogCustom($strKey,$strContent){
			$this->add('og',$strKey,$strContent);
		}

		public function generate(){

			$strOut = '';

			foreach($this->arrTags as $arrEachTag) {

				switch ($arrEachTag['type']) {
					case'title':
						$strOut .= sprintf("<title>%s</title>", $arrEachTag['value']);
						break;

					case'meta':

						if ($arrEachTag['name'] == 'charset') {
							$strOut .= sprintf('<meta charset="%s">', $arrEachTag['value']);
						} else {
							$strOut .= sprintf('<meta name="%s" content="%s">', $arrEachTag['name'], $arrEachTag['value']);
						}

						break;

					case'og':
						$strOut .= sprintf('<meta property="og:%s" content="%s">', $arrEachTag['name'], $arrEachTag['value']);
						break;
				}

				return $strOut;
			}

		}


	}