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

	namespace Twist\Core\Helpers;

	/**
	 * Connect to an FTP server via PHP with the ability to browse and create directories, upload, download and delete files.
	 * The ability to choose Passive and Active connection mode is also present if using Native support.
	 */
	class FTP extends Base{

		protected $resLibrary = null;
		protected $intTimeout = 90;
		protected $arrFeatures = array();

		public function __construct(){

			$strLibraryClass = sprintf('\Twist\Core\Models\FTP\%s',ucfirst(\Twist::framework()->setting('FTP_LIBRARY'))); //Can be set to either 'ftpnative' or 'ftpsocket'

			if(!class_exists($strLibraryClass)){
				throw new \Exception(sprintf("Error, FTP protocol library '%s' is not installed or supported",\Twist::framework()->setting('FTP_LIBRARY')));
			}

			$this->resLibrary = new $strLibraryClass();
		}

		/**
		 * Get any error message that has been returned as the result of a failed command
		 */
		public function getMessage(){
			return $this->resLibrary->getMessage();
		}

		/**
		 * Connect to the remote FTP server
		 * @param string $strHost
		 * @param int $intPort
		 * @param null|int $intConnectionTimeout
		 */
		public function connect($strHost,$intPort = 21,$intConnectionTimeout = null){

			//Set the connection timeout
			$this->resLibrary->setTimeout($intConnectionTimeout);
			return $this->resLibrary->connect($strHost,$intPort);
		}

		/**
		 * Disconnect from the remote FTP server
		 */
		public function disconnect(){
			$this->resLibrary->disconnect();
			$this->arrFeatures = array();
		}

		/**
		 * Login to the open FTP connection
		 * @param string $strUsername
		 * @param string $strPassword
		 * @return bool
		 */
		public function login($strUsername,$strPassword){
			return $this->resLibrary->login($strUsername,$strPassword);
		}

		/**
		 * Enable/Disable passive mode globally for this connection
		 * @param bool $blEnable
		 */
		public function passiveMode($blEnable = true){
			$this->resLibrary->pasv($blEnable);
		}

		/**
		 * Get the system name for the FTP connection
		 * @return bool
		 */
		public function systemName(){
			return $this->resLibrary->systype();
		}

		/**
		 * Get an array of supported features for the current FTP server connection
		 * @return array|bool
		 */
		public function featureList(){
			return (count($this->arrFeatures)) ? $this->arrFeatures : $this->arrFeatures = $this->resLibrary->feat();
		}

		/**
		 * Detect if the connected FTP server supports this feature
		 * @param string $strFeature Name of feature to check
		 * @return bool
		 */
		public function featureSupported($strFeature){
			$arrFeatures = $this->featureList();
			return array_key_exists($strFeature,$arrFeatures);
		}

		/**
		 * Get path of the current working directory on the remote FTP server
		 * @return string Returns the directory path
		 */
		public function getCurrentDirectory(){
			return $this->resLibrary->pwd();
		}

		/**
		 * Change the current working directory to a new location
		 *
		 * @param string $strDirectory Path of new working directory
		 * @return bool Returns the status of change
		 */
		public function changeDirectory($strDirectory){
			return $this->resLibrary->cwd($strDirectory);
		}

		/**
		 * Detect if the directory exists and is a directory
		 *
		 * @param string $strDirectory Path of directory
		 * @return bool Returns the status of directory
		 */
		public function isDirectory($strDirectory){
			return is_array($this->resLibrary->nlist($strDirectory));
		}

		/**
		 * Make a new directory on the remote FTP server
		 *
		 * @param string $strDirectory Path for the new directory
		 * @return bool Returns the status of directory creation
		 */
		public function makeDirectory($strDirectory){

			if(substr($strDirectory, 0, 1) == '/'){
				//Full Path Create
				$this->changeDirectory('/');
			}

			$arrDirectoryParts = explode('/', trim($strDirectory, '/'));
			foreach($arrDirectoryParts as $strSubDir){
				if($this->listDirectory($strSubDir) === false){
					$this->resLibrary->mkd($strSubDir);
				}
				$this->changeDirectory($strSubDir);
			}

			return true;
		}

		/**
		 * Remove a directory on the remote FTP server
		 *
		 * @param string $strDirectory Path of the directory to remove
		 * @return bool Returns the status of the removal
		 */
		public function removeDirectory($strDirectory){

			if($this->isDirectory($strDirectory)){

				$arrFiles = $this->resLibrary->nlist($strDirectory);
				if(is_array($arrFiles)){
					foreach($arrFiles as $strFile){
						if(!in_array($strFile,array('.','..'))){
							if($this->isDirectory($strFile)){
								$this->removeDirectory($strFile);
							}else{
								$this->delete($strFile);
							}
						}
					}
				}

				$this->delete($strDirectory);
			}

			return false;
		}

		/**
		 * List the provided directory and return as an array
		 *
		 * @param string $strDirectory
		 * @return array|bool
		 */
		public function listDirectory($strDirectory){
			return $this->resLibrary->nlist($strDirectory);
		}

		/**
		 * Rename either a file or directory to a new name
		 *
		 * @param string $strFilename
		 * @param string $strNewFilename
		 * @return bool
		 */
		public function rename($strFilename, $strNewFilename){
			return $this->resLibrary->rename($strFilename,$strNewFilename);
		}

		/**
		 * Upload a file to the remote FTP server
		 *
		 * @param string $strLocalFilename
		 * @param string $strRemoteFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function upload($strLocalFilename, $strRemoteFilename, $strMode = 'A'){
			return $this->resLibrary->upload($strLocalFilename,$strRemoteFilename,$strMode);
		}

		/**
		 * Download a file from the remote FTP server
		 *
		 * @param string $strRemoteFilename
		 * @param string $strLocalFilename
		 * @param string $strMode
		 * @return bool
		 */
		public function download($strRemoteFilename, $strLocalFilename, $strMode = 'A'){
			return $this->resLibrary->download($strRemoteFilename,$strLocalFilename,$strMode);
		}

		/**
		 * Remove the file from the server
		 *
		 * @param string $strFilename
		 * @return bool
		 */
		public function delete($strFilename){
			return $this->resLibrary->delete($strFilename);
		}

		/**
		 * CHMOD the files permissions
		 *
		 * @param string $strFilename
		 * @param integer $intMode
		 * @return bool
		 */
		public function chmod($strFilename,$intMode){
			return $this->resLibrary->chmod($strFilename,$intMode);
		}

		/**
		 * Get the size of any given file on the remote FTP server
		 *
		 * @param string $strFilename
		 * @return bool|int
		 */
		public function size($strFilename){
			return ($this->featureSupported('SIZE')) ? $this->resLibrary->size($strFilename) : false;
		}

		/**
		 * Get the last modified time of any given file on the remote FTP server
		 *
		 * @param string $strFilename
		 * @return bool|int
		 */
		public function modified($strFilename){
			return ($this->featureSupported('MDTM')) ? $this->resLibrary->mdtm($strFilename) : false;
		}
	}