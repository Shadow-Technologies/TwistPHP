<?php

	/**
	 * TwistPHP - An open source PHP MVC framework built from the ground up.
	 * Shadow Technologies Ltd.
	 *
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license    https://www.gnu.org/licenses/gpl.html GPL License
	 * @link       https://twistphp.com
	 */

	namespace Twist\Core\Models;

	use Twist\Classes\Instance;

    /**
	 * Framework hooks that are used throughout the framework, see the docs for a full list of integrated hocks.
	 * Register a hook to add in new view tags or extend framework functionality. Custom hooks are available in some packages to all them to be extended easily.
	 * The default framework hooks are loaded in the construct
	 */
	class Hooks{

		protected $arrPermanentHooks = array();
		protected $arrHooks = array();

		public function __construct(){

			$this->loadHooks();

			//Register the default Twist helper extensions
			$this->arrHooks['TWIST_VIEW_TAG']['asset'] = array('module' => 'Asset','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['file'] = array('module' => 'File','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['session'] = array('module' => 'Session','function' => 'viewExtension');
			$this->arrHooks['TWIST_VIEW_TAG']['user'] = array('module' => 'User','function' => 'viewExtension');

			//Register the framework message handler into the template system
			$this->arrHooks['TWIST_VIEW_TAG']['messages'] = array('core' => 'messageHandler');

			//Register the framework resources handler into the template system
			$this->arrHooks['TWIST_VIEW_TAG']['resource'] = array('instance' => 'twistCoreResources','function' => 'viewResource');
			$this->arrHooks['TWIST_VIEW_TAG']['css'] = array('instance' => 'twistCoreResources','function' => 'viewCSS');
			$this->arrHooks['TWIST_VIEW_TAG']['js'] = array('instance' => 'twistCoreResources','function' => 'viewJS');
			$this->arrHooks['TWIST_VIEW_TAG']['img'] = array('instance' => 'twistCoreResources','function' => 'viewImage');
			$this->arrHooks['TWIST_VIEW_TAG']['placeholder'] = array('instance' => 'twistCoreResources','function' => 'viewPlaceholder');

			//Integrate the basic core href tag support
			$strResourcesURI = sprintf('%s/%sCore/Resources/',rtrim(SITE_URI_REWRITE,'/'),ltrim(TWIST_FRAMEWORK_URI,'/'));

			$this->arrHooks['TWIST_VIEW_TAG']['core'] = array(
				'logo' => sprintf('%slogos/logo.png',$strResourcesURI),
				'logo-favicon' => sprintf('%slogos/favicon.ico',$strResourcesURI),
				'logo-32' => sprintf('%slogos/logo-32.png',$strResourcesURI),
				'logo-48' => sprintf('%slogos/logo-48.png',$strResourcesURI),
				'logo-57' => sprintf('%slogos/logo-57.png',$strResourcesURI),
				'logo-64' => sprintf('%slogos/logo-64.png',$strResourcesURI),
				'logo-72' => sprintf('%slogos/logo-72.png',$strResourcesURI),
				'logo-96' => sprintf('%slogos/logo-96.png',$strResourcesURI),
				'logo-114' => sprintf('%slogos/logo-114.png',$strResourcesURI),
				'logo-128' => sprintf('%slogos/logo-128.png',$strResourcesURI),
				'logo-144' => sprintf('%slogos/logo-144.png',$strResourcesURI),
				'logo-192' => sprintf('%slogos/logo-192.png',$strResourcesURI),
				'logo-256' => sprintf('%slogos/logo-256.png',$strResourcesURI),
				'logo-512' => sprintf('%slogos/logo-512.png',$strResourcesURI),
				'logo-640' => sprintf('%slogos/logo-640.png',$strResourcesURI),
				'logo-800' => sprintf('%slogos/logo-800.png',$strResourcesURI),
				'logo-1024' => sprintf('%slogos/logo-1024.png',$strResourcesURI),
				'logo-large' => sprintf('%slogos/logo-512.png',$strResourcesURI),
				'logo-small' => sprintf('%slogos/logo-32.png',$strResourcesURI),
				'resources_uri' => $strResourcesURI,
				'uri' => ltrim(sprintf('%s/%s',rtrim(SITE_URI_REWRITE,'/'),ltrim(TWIST_FRAMEWORK_URI,'/')),'/')
			);

			//Register the Email Protocol
			$this->arrHooks['TWIST_EMAIL_PROTOCOLS']['native'] = array('model' => 'Twist\Core\Models\Email\Send');
		}

		/**
		 * Register a hook to extend framework or package functionality
		 * @param string $strHook
		 * @param mixed $mxdUniqueKey
		 * @param mixed $mxdData
		 * @param bool $blPermanent
		 */
		public function register($strHook,$mxdUniqueKey,$mxdData,$blPermanent = false){

			if(!array_key_exists($strHook,$this->arrHooks)){
				$this->arrHooks[$strHook] = array();
			}

			$this->arrHooks[$strHook][$mxdUniqueKey] = $mxdData;

			if($blPermanent){
				$this->storeHook($strHook,$mxdUniqueKey,$mxdData);
			}
		}

		/**
		 * Cancel a hook from being active in the system, this will cancel the hook form the current page load only
		 * @param string $strHook
		 * @param mixed $mxdUniqueKey
		 * @param bool $blPermanent
		 */
		public function cancel($strHook,$mxdUniqueKey,$blPermanent = false){

			unset($this->arrHooks[$strHook][$mxdUniqueKey]);

			if($blPermanent){
				$this->removeHook($strHook,$mxdUniqueKey);
			}
		}

		/**
		 * Get the array of extensions for the requested hook and key
		 * @param string $strHook
		 * @param mixed $mxdUniqueKey
		 * @return array
		 */
		public function get($strHook,$mxdUniqueKey){
			return (array_key_exists($strHook,$this->arrHooks)) ? $this->arrHooks[$strHook][$mxdUniqueKey] : array();
		}

		/**
		 * Get all the hooks, you can filter by package or leave blank for everything
		 * @param string $strHook
		 * @return array
		 */
		public function getAll($strHook = null){

			if(is_null($strHook)){
				return $this->arrHooks;
			}

			return (array_key_exists($strHook,$this->arrHooks)) ? $this->arrHooks[$strHook] : array();
		}

		/**
		 * Load the hooks form storage area
		 */
		protected function loadHooks(){

			$this->arrPermanentHooks = array();

			if(\Twist::Database()->isConnected()){

				$arrCachedHooks = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX.'hooks')->find();

				foreach($arrCachedHooks as $arrEachHook){

					if(!array_key_exists($arrEachHook['hook'],$this->arrPermanentHooks)){
						$this->arrPermanentHooks[$arrEachHook['hook']] = array();
					}

					$this->arrPermanentHooks[$arrEachHook['hook']][$arrEachHook['key']] = json_decode($arrEachHook['data'],true);
				}
			}else{
				//Fallback for those that are not using a Database
				$arrCacheData = \Twist::Cache('twist/hooks')->read('permanent');
				if(!is_null($arrCacheData)){
					$this->arrPermanentHooks = $arrCacheData;
				}
			}

			if(count($this->arrHooks) == 0){
				$this->arrHooks = $this->arrPermanentHooks;
			}else{
				$this->arrHooks = \Twist::framework()->tools()->arrayMergeRecursive($this->arrHooks,$this->arrPermanentHooks);
			}
		}

		/**
		 * Permanently store a new hook
		 * @param string $strHook
		 * @param mixed $mxdUniqueKey
		 * @param mixed $mxdData
		 */
		protected function storeHook($strHook,$mxdUniqueKey,$mxdData){

			if(!array_key_exists($strHook,$this->arrPermanentHooks)){
				$this->arrPermanentHooks[$strHook] = array();
			}

			$this->arrPermanentHooks[$strHook][$mxdUniqueKey] = $mxdData;

			if(\Twist::Database()->isConnected()) {
				$resRecord = \Twist::Database()->records(TWIST_DATABASE_TABLE_PREFIX . 'hooks')->create();
				$resRecord->set('hook', $strHook);
				$resRecord->set('key', $mxdUniqueKey);
				$resRecord->set('data', json_encode($mxdData));
				$resRecord->set('registered', date('Y-m-d H:i:s'));
				$resRecord->commit();
			}else{
				//Fallback for those that are not using a Database
				\Twist::Cache('twist/hooks')->write('permanent',$this->arrPermanentHooks,(3600*24)*365);
			}
		}

		/**
		 * Remove a permanently stored hook, only wo
		 * @param string $strHook
		 * @param mixed $mxdUniqueKey
		 */
		protected function removeHook($strHook,$mxdUniqueKey){

			//Remove the hook from the permanent array
			unset($this->arrPermanentHooks[$strHook][$mxdUniqueKey]);

			if(\Twist::Database()->isConnected()) {
				\Twist::Database()->query("DELETE FROM `%s`.`%s` WHERE `hook` = '%s' AND `key` = '%s' LIMIT 1",
					TWIST_DATABASE_NAME,
					TWIST_DATABASE_TABLE_PREFIX . 'hooks',
					$strHook,
					$mxdUniqueKey
				);
			}else{
				//Fallback for those that are not using a Database
				\Twist::Cache('twist/hooks')->write('permanent',$this->arrPermanentHooks,(3600*24)*365);
			}
		}

        /**
         * Run a specific hook and return the result
         * @param $strHook
         * @param $mxdUniqueKey
         * @param array $arrArguments
         * @return array|false|mixed|null
         */
        public function runHook($strHook,$mxdUniqueKey,$arrArguments = []){

            $mxdOut = null;
            $arrHook = $this->get($strHook,$mxdUniqueKey);
            if(count($arrHook)){

                if(array_key_exists('core',$arrHook)){

                    $strClassName = $arrHook['core'];

                    $mxdOut = call_user_func_array(array('\Twist',$strClassName), $arrArguments);
                    //$mxdOut = \Twist::$strClassName($strReference,$arrParameters);

                }elseif(array_key_exists('module',$arrHook)){

                    $strClassName = $arrHook['module'];
                    $strFunctionName = $arrHook['function'];

                    $mxdOut = call_user_func_array(array('\Twist::'.$strClassName,$strFunctionName), $arrArguments);
                    //$mxdOut = \Twist::$strClassName() -> $strFunctionName($strReference,$arrParameters);

                }elseif(array_key_exists('class',$arrHook)){

                    $strClassName = sprintf('\\%s',$arrHook['class']);
                    $strFunctionName = $arrHook['function'];

                    $objClass = new $strClassName();
                    $mxdOut = call_user_func_array(array($objClass,$strFunctionName), $arrArguments);
                    //$mxdOut = $objClass -> $strFunctionName($strReference,$arrParameters,$arrData);

                }elseif(array_key_exists('instance',$arrHook)){

                    $resClass = Instance::retrieveObject($arrHook['instance']);
                    $strFunctionName = $arrHook['function'];

                    $mxdOut = call_user_func_array(array($resClass,$strFunctionName), $arrArguments);
                    //$mxdOut = $resClass -> $strFunctionName($strReference,$arrParameters);

                }elseif(array_key_exists('function',$arrHook)){

                    //@note Does not accept the params array at the moment, may deprecate this option
                    $strFunctionName = $arrHook['function'];

                    $mxdOut = call_user_func_array($strFunctionName, $arrArguments);
                    //$mxdOut = call_user_func($strFunctionName,$strReference);
                }else{
                    $mxdOut = $arrHook;
                }
            }

            return $mxdOut;
        }
	}