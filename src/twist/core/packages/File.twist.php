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
 * @author    Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license   https://www.gnu.org/licenses/gpl.html LGPL License
 * @link      http://twistphp.com/
 *
 */

namespace TwistPHP\Packages;
use TwistPHP\ModuleBase;

/**
 * Additional file system support that you have always needed but never had. Convert bytes into human readable format, sanitize file names, hash a directory or get its total size in bytes.
 * Recursive file and folder manipulation and the ability to serve files even apply download speed restrictions when running a high traffic site and bandwidth is precious.
 */
class File extends ModuleBase{

	protected $resTemplate = null;
	protected $strAssetDirectory = null;

	/**
	 * Load up an instance of the template class for when it is required
	 */
	public function __construct(){
		$this->resTemplate = \Twist::Template('pkgFile');
		$this->resTemplate->setTemplatesDirectory( sprintf('%s/templates/File/',DIR_FRAMEWORK_PACKAGES));
	}

	/**
	 * Convert bytes to a human readable size for example 1536 would be converted to 1.5KB
	 *
	 * @param $intBytes Size in bytes
	 * @return mixed Returns a human readable data size
	 */
	public function bytesToSize($intBytes){

		$strOut = '';

		$intKilobyte = 1024;
		$intMegabyte = $intKilobyte * 1024;
		$intGigabyte = $intMegabyte * 1024;
		$intTerabyte = $intGigabyte * 1024;

		if(($intBytes >= 0) && ($intBytes < $intKilobyte)){
			$strOut = sprintf('%sB',$intBytes);
		}elseif(($intBytes >= $intKilobyte) && ($intBytes < $intMegabyte)){
			//For Kilobytes make sure to remove odd bits as it is not nessasery to see all decimals at this level
			$intKilobytesFull = round($intBytes / $intKilobyte, 2);
			switch(strlen($intKilobytesFull)){
				case 7:
				case 6:
					$strOut = sprintf('%skB',round($intBytes / $intKilobyte, 0));
					break;
				case 5:
					$strOut = sprintf('%skB',round($intBytes / $intKilobyte, 1));
					break;
				case 4:
				default:
					$strOut = sprintf('%skB',round($intBytes / $intKilobyte, 2));
					break;
			}
		}elseif(($intBytes >= $intMegabyte) && ($intBytes < $intGigabyte)){
			$strOut = sprintf('%sMB',round($intBytes / $intMegabyte, 2));
		}elseif(($intBytes >= $intGigabyte) && ($intBytes < $intTerabyte)){
			$strOut = sprintf('%sGB',round($intBytes / $intGigabyte, 2));
		}elseif($intBytes >= $intTerabyte) {
			$strOut = sprintf('%sTB',round($intBytes / $intTerabyte, 2));
		}else{
			$strOut = $intBytes . 'B';
		}
		return str_replace('.00','',$strOut);
	}

	/**
	 * Sanitize a file name to make it more user friendly. Also helps to prevent errors and make a much cleaner file system.
	 *
	 * @param $strFilename Name to be sanitized
	 * @param $blIsFilename Set to true will allow '~' and '.' in file names
	 * @return string Returns the sanitized file name
	 */
	public function sanitizeName($strFilename, $blIsFilename = true){

		//Trim out the non required items
		$strFilename = trim($strFilename," -");

		$strFilename = preg_replace('/[^a-z0-9\s\-_'. ($blIsFilename ? '~\.' : '').']/i','',$strFilename);
		$strFilename = preg_replace('/\s{2,}/',' ',$strFilename);
		$strFilename = preg_replace('/\s/','-',$strFilename);
		$strFilename = preg_replace('/\-{2,}/','-',$strFilename);

		// Only allow one dash separator at a time (and make string lowercase)
		return mb_strtolower(preg_replace('/--+/u', '-', $strFilename), 'UTF-8');
	}


	/**
	 * Get the file extension of any file, provide the file or its full path.
	 *
	 * @param $strFilePath File name/path
	 * @return string Returns the file extension
	 */
	public function extension($strFilePath){

		if(function_exists('pathinfo')){
			$arrData = pathinfo($strFilePath);
			$strExtension = $arrData['extension'];
		}else{
			$strExtension = ltrim(substr(strrchr($strFilePath,'.'),1),'.');
		}

		return $strExtension;
	}

	/**
	 * Get the filename, trim off any path information that is not required.
	 *
	 * @param $strFile Full path to file including file name
	 * @return string Returns the file name only
	 */
	public function name($strFile){

		if(strstr($strFile,'/')){
			$arrParts = explode('/',$strFile);
			$strFile = array_pop($arrParts);
		}

		return $strFile;
	}

	/**
	 * Find a file in a directory when there is multiple of the same file with many different version numbers.
	 *
	 * @param $strDirectory Path of directory to search
	 * @param $strFilePrefix File prefix to help filter correct files
	 * @param $strVersion Version of file to find
	 * @return string Returns file name of verion file
	 */
	public function findVersion($strDirectory,$strFilePrefix,$strVersion = null){

		$strOut = null;
		$arrFiles = scandir(BASE_LOCATION.$strDirectory);

		if(is_null($strVersion) || $strVersion == '' || $strVersion == 'latest'){

			$arrOptions = array();

			//Run through and create the version array
			foreach($arrFiles as $strEachFile){

				//Only pick out the correct files that contain the prefix
				if(strstr($strEachFile,$strFilePrefix) && !in_array($strEachFile,array('.','..'))){

					$arrParts = explode('-',$strEachFile);
					$arrVersionNos = explode('.',array_pop($arrParts));

					//Only log files with 4 parts
					if(count($arrVersionNos) == 4){
						$arrOptions[$arrVersionNos[0]][$arrVersionNos[1]][$arrVersionNos[2]] = $strEachFile;
					}
				}
			}

			//Boil down to the latest version
			ksort($arrOptions);
			$arrTopVersion = array_pop($arrOptions);
			ksort($arrTopVersion);
			$arrTopMajor = array_pop($arrTopVersion);
			ksort($arrTopMajor);
			$strOut = array_pop($arrTopMajor);

		}else{

			foreach($arrFiles as $strEachFile){
				if(strstr($strEachFile,sprintf('%s-%s',$strEachFile,$strVersion))){
					$strOut = $strEachFile;
				}
			}
		}

		if(!is_null($strOut)){
			$strOut = sprintf('%s%s',$strDirectory,$strOut);
		}

		return $strOut;
	}

	/**
	 * Serve any local file to the user to be downloaded. Mime type, Max Cache Time and Restricted Download Speed in KB are all optional
	 *
	 * @param $strFile Full path of file to be served
	 * @param $strServeAsName Serve the file as the name provided
	 * @param $strMimeType Mime type to serve file as
	 * @param $intMaxCacheTime Set to the max cache time in seconds
	 * @param $intMaxTransferRate Set to the Max transfer rate in kb/s
	 * @param $blDeleteFile Remove the file after serve, use this when serving a temp file
	 */
	public function serve($strFile,$strServeAsName=null,$strMimeType=null,$intMaxCacheTime=null,$intMaxTransferRate=null,$blDeleteFile=false){

		if(file_exists($strFile)){

			$intFileModifiedTime=filemtime($strFile);

			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $intFileModifiedTime)){
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', $intFileModifiedTime).' GMT', true, 304);
			}else{

				//Fix the IE Shite Browser Download
				if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}

				$strMimeType = (is_null($strMimeType)) ? 'application/force-download' : $strMimeType;
				header(sprintf("Content-Type: %s",$strMimeType));

				if(!is_null($strServeAsName)){
					$strFileBase = $this->sanitizeName($strServeAsName);
				}else{
					$strFileBase = basename($strFile);
					$strFileBase = $this->sanitizeName($strFileBase);
				}

				//Set the inital headers
				header("Content-Transfer-Encoding: binary");
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename='.$strFileBase);
				header("Content-Length: ".filesize($strFile));

				//Fix for IE6, IE7 and IE8 browsers when using HTTPS that do not have the following HotFix: http://support.microsoft.com/kb/323308/en-us
				if(preg_match('/MSIE ([6-8]{1}[^;]*);/',$_SERVER['HTTP_USER_AGENT'],$arrMatches) && !empty($_SERVER['HTTPS'])){
					header("Cache-Control: private");
					header("Pragma: private");
				}else{

					if(is_null($intMaxCacheTime)){
						//If no cache time set don't allow caching
						header('Pragma: no-cache');
						header('Expires: 0');
					}else{
						//Server all the cache headers for the file
						header('Pragma: public');
						header('Cache-Control: public, maxage='.$intMaxCacheTime);
						header('Expires: ' . gmdate('D, d M Y H:i:s', \Twist::DateTime()->time()+$intMaxCacheTime) . ' GMT');
					}
				}

				header('Last-Modified: '.gmdate('D, d M Y H:i:s', $intFileModifiedTime).' GMT', true, 200);

				//flush content
				flush();

				if(is_null($intMaxTransferRate)){
					//Read the file to the user as fast as possible
					readfile($strFile);

					if($blDeleteFile){
						unlink($strFile);
					}
				}else{

					// open file stream
					$resFile = fopen($strFile, "r");

					if(is_resource($resFile)){

						while(!feof($resFile)){

							// send the current file part to the browser
							print fread($resFile, round(($intMaxTransferRate / 4) * 1024));

							// flush the content to the browser
							flush();

							// sleep one second (1000000 == 1 second)
							usleep(250000);
							// -- sleep was abit jerky in the browser side sleep(1);
						}

						// close file stream
						fclose($resFile);

						if($blDeleteFile){
							unlink($strFile);
						}
					}else{
						//header("HTTP/1.1 500 Internal Server Error");
						\TwistPHP\Error::errorPage(500);
					}
				}
			}
		}else{
			//header("HTTP/1.1 404 Not Found");
			\TwistPHP\Error::errorPage(404);
		}

		die();
	}

	/**
	 * Handle uploaded files, call the function and pass in the html file input name. The file will then be uploaded to the system ready to be processed.
	 * Optionally pass in a UID so that you can reference the temp file to further process the file, can be useful if uploading a file before the user has submitted the form.
	 *
	 * @param $strFileKey Key for the file in the $_FILES array
	 * @param $strUID Unique ID used to reference the file after upload
	 * @return array Returns an array of information for the uploaded file
	 */
	public function upload($strFileKey,$strUID = null){

		if(is_null($strUID)){
			$strUID = uniqid();
		}

		$arrOut = array(
			'status' => false,
			'error' => '',
			'file' => array(
				'UID' => $strUID,
				'name' => '',
				'size' => '',
				'path' => '',
			)
		);

		//Create an upload directory for uploaded files
		$strUploadFolder = sprintf('%s/uploads',BASE_LOCATION);

		if(!is_dir($strUploadFolder)){
			mkdir($strUploadFolder);
		}

		if(!is_null($strUID) && $strUID != ''){
			if(is_array($_FILES) && array_key_exists($strFileKey,$_FILES)){

				$arrFileData = $_FILES[$strFileKey];

				if($arrFileData['error'] == 0){

					$intMegabytes = str_replace('M','',$this->framework()->setting('FILE_MAX_UPLOAD_SIZE'));
					$intMaxSizeInBytes = (($intMegabytes*1024)*1024);

					if($arrFileData['size'] <= $intMaxSizeInBytes){

						$strExtension = $this->extension($arrFileData['name']);

						$strNewFilename = $this->sanitizeName($arrFileData['name']);
						$strNewFilename = str_replace('.'.strtolower($strExtension),'',$strNewFilename);

						$strFile = sprintf('uid%s_%s-%s.%s',$strUID,$strNewFilename,\Twist::DateTime()->time(),strtolower($strExtension));
						move_uploaded_file($arrFileData['tmp_name'],sprintf('%s/%s',$strUploadFolder,$strFile));

						//Set the output data of the uploaded file
						$arrOut['file']['size'] = $arrFileData['size'];
						$arrOut['file']['name'] = $strFile;
						$arrOut['file']['path'] = sprintf('%s/%s',$strUploadFolder,$strFile);
						$arrOut['status'] = true;
					}else{
						$arrOut['error'] = sprintf("Uploaded file size to large, max file size of %s",$this->framework()->setting('FILE_MAX_UPLOAD_SIZE'));
					}
				}else{
					$arrOut['error'] = $_FILES[$strFileKey]["error"];
				}
			}else{
				$arrOut['error'] = sprintf("File key '%s' has not been posted",$strFileKey);
			}
		}else{
			$arrOut['error'] = sprintf("UID has not been set correctly",$strFileKey);
		}

		if($arrOut['error'] != ''){
			trigger_error(sprintf("Twist File Handler: %s",$arrOut['error']));
		}

		return $arrOut;
	}

	/**
	 * Upload a file the easy way with the new AJAX PUT upload function by TwistPHP. Use the template tags and provided JS files to achieve simple file uploads.
	 *
	 * @related upload
	 * @return array Returns an array of information for the uploaded file
	 */
	public function uploadPUT(){

		$arrOut = array(
			'status' => false,
			'error' => '',
			'file' => array(
				'UID' => '',
				'name' => '',
				'size' => 0,
				'path' => '',
			),
			'preview' => ''
		);

		//Create an upload directory for uploaded files
		$strUploadFolder = sprintf('%s/uploads',BASE_LOCATION);

		if(!is_dir($strUploadFolder)){
			mkdir($strUploadFolder);
		}

		$strFileData = file_get_contents("php://input");

		if(strlen($strFileData) > 0){

			if(array_key_exists('HTTP_TWIST_FILE',$_SERVER) && array_key_exists('HTTP_TWIST_UID',$_SERVER)){
				$arrOut['file']['UID'] = $_SERVER['HTTP_TWIST_UID'];
				$arrOut['file']['name'] = $_SERVER['HTTP_TWIST_FILE'];
				$arrOut['file']['size'] = strlen($strFileData);
				$arrOut['file']['path'] = sprintf('%s/%s',$strUploadFolder,$_SERVER['HTTP_TWIST_FILE']);

				file_put_contents($arrOut['file']['path'],$strFileData);
				$arrOut['status'] = true;
			}else{
				$arrOut['error'] = 'Incorrect file headers provided';
			}
		}else{
			$arrOut['error'] = 'No file data has been uploaded';
		}

		return $arrOut;
	}

	/**
	 * Basic alias function of PHP's hash_file, hash a file on the local server
	 *
	 * @reference http://php.net/manual/en/function.hash-file.php
	 * @param $strFilePath Path to the file
	 * @param $strHashAlgorithm Set the hash algorithm 'md5' or 'sha1'
	 * @return string Returns a hash of the file
	 */
	public function hash($strFilePath, $strHashAlgorithm='md5'){
		return hash_file($strHashAlgorithm,$strFilePath);
	}

	/**
	 * Get a unique Hash of a directory in MD5 or SHA1. If any single item within the directory or sub-directories changes the unique hash will change as well.
	 *
	 * @related hash
	 * @param $strDirectoryPath Path of the directory
	 * @param $strHashAlgorithm Set the hash algorithm 'md5' or 'sha1'
	 * @return bool|string
	 */
	public function directoryHash($strDirectoryPath, $strHashAlgorithm='md5'){

		$arrHashes = array();

		if(is_dir($strDirectoryPath)){

			$arrHashes = array();
			$resDir = dir($strDirectoryPath);

			while(false !== ($strEntry = $resDir->read())){
				if($strEntry != '.' && $strEntry != '..'){
					if(is_dir($strDirectoryPath.'/'.$strEntry)){
						$arrHashes[] = $this->directoryHash($strDirectoryPath.'/'.$strEntry,$strHashAlgorithm);
					}else{
						$arrHashes[] = $this->hash($strDirectoryPath.'/'.$strEntry,$strHashAlgorithm);
					}
				}
			}

			$resDir->close();
		}

		return ($strHashAlgorithm == 'md5') ? md5(implode('', $arrHashes)) : sha1(implode('', $arrHashes));
	}

	/**
	 * Get the full size in bytes of any directory by providing its full path. Optional parameter to format the return data in a human readable format.
	 *
	 * @param $strDirectoryPath Path of the directory
	 * @param $blFormatOutput Set 'true' to format output
	 * @return mixed Returns the size in bytes or a human readable format
	 */
	public function directorySize($strDirectoryPath, $blFormatOutput=false){

		$intSizeBytes = 0;

		if(is_dir($strDirectoryPath)){

			$resDir = dir($strDirectoryPath);

			while(false !== ($strEntry = $resDir->read())){
				if ($strEntry != '.' && $strEntry != '..'){
					if (is_dir($strDirectoryPath.'/'.$strEntry)){
						$intSizeBytes += $this->directorySize($strDirectoryPath.'/'.$strEntry);
					}else{
						$intSizeBytes += filesize($strDirectoryPath.'/'.$strEntry);
					}
				}
			}
			$resDir->close();
		}

		return ($blFormatOutput) ? $this->bytesToSize($intSizeBytes) : $intSizeBytes;
	}

	/**
	 * Basic alias function of PHP's unlink, removes a file or symlink from the local server
	 *
	 * @reference http://php.net/manual/en/function.unlink.php
	 * @param $strFilePath Path of the file to be removed
	 * @return bool Return the status of the removal
	 */
	public function remove($strFilePath){
		return (file_exists($strFilePath) || is_link($strFilePath)) ? unlink($strFilePath) : false;
	}

	/**
	 * @alias remove
	 */
	public function delete($strFilePath){ return $this->remove($strFilePath); }

	/**
	 * Recursively remove a directory and all its files and sub directories on the local server
	 *
	 * @related remove
	 * @param $strDirectory Path of the directory to be removed
	 */
	public function recursiveRemove($strDirectory){

		if(file_exists($strDirectory) && is_dir($strDirectory)){

			$arrFiles = scandir($strDirectory);

			foreach ($arrFiles as $strEachFile){
				($strEachFile != "." && $strEachFile != "..") ? $this->recursiveRemove("$strDirectory/$strEachFile") : null;
			}

			if(file_exists($strDirectory) && is_dir($strDirectory)){
				rmdir($strDirectory);
			}

		}elseif(file_exists($strDirectory) || is_link($strDirectory)){
			$this->remove($strDirectory);
		}
	}

	/**
	 * Basic alias function of PHP's rename, move/rename a file on the local server
	 *
	 * @reference http://php.net/manual/en/function.rename.php
	 * @param $strSourcePath Path of file to be moved
	 * @param $strDestinationPath Destination path and name for moved file
	 * @return bool Returns the status of the move
	 */
	public function move($strSourcePath, $strDestinationPath){
		return rename($strSourcePath, $strDestinationPath);
	}

	/**
	 * Basic alias function of PHP's copy, copy a file on the local server
	 *
	 * @reference http://php.net/manual/en/function.copy.php
	 * @param $strSourcePath Path of file to be copied
	 * @param $strDestinationPath Destination path and name for copied file
	 * @return bool Returns the status of the copied
	 */
	public function copy($strSourcePath,$strDestinationPath){
		return copy($strSourcePath, $strDestinationPath);
	}

	/**
	 * Recursively copy a directory and all its files and sub-directories to a new location on the local server
	 *
	 * @related copy
	 * @param $strSource
	 * @param $strDestination
	 */
	public function recursiveCopy($strSourcePath,$strDestinationPath){

		//Remove data from the destination if already exists
		(file_exists($strDestinationPath)) ? $this->recursiveRemove($strDestinationPath) : null;

		if(is_dir($strSourcePath)){

			mkdir($strDestinationPath);
			$arrFiles = scandir($strSourcePath);

			//For each file and folder in the array recursively copy it
			foreach($arrFiles as $strEachFile){
				if($strEachFile != "." && $strEachFile != ".."){
					//Recursively copy as this is another directory
					$this->recursiveCopy("$strSourcePath/$strEachFile", "$strDestinationPath/$strEachFile");
				}
			}
		}elseif(file_exists($strSourcePath)){
			//If the source is a file copy to the destination
			$this->copy($strSourcePath, $strDestinationPath);
		}
	}

	/**
	 * Recursively create a directory on the local server
	 *
	 * @param $strDirectoryPath New directory path
	 * @return boolean Returns that status of the new directory
	 */
	public function recursiveCreate($strDirectoryPath){

		if(!file_exists($strDirectoryPath)){
			$arrDirectoryParts = explode('/',trim($strDirectoryPath,'/'));
			$strNewDirectory = '';

			foreach($arrDirectoryParts as $strEachFolder){

				$strNewDirectory .= sprintf('/%s',$strEachFolder);
				if(!file_exists($strNewDirectory)){
					mkdir($strNewDirectory);
				}
			}
		}

		return file_exists($strDirectoryPath);
	}

	/**
	 * Output the HTML upload form and relevant javascript
	 *
	 * Template Tags:
	 * upload
	 * upload-html
	 * upload-init
	 * upload-js
	 *
	 * Parameter Order:
	 * {file:upload}
	 * {file:upload,name}
	 * {file:upload,name,multiple}
	 * {file:upload,name,multiple,id}
	 *
	 * Parameter Contents:
	 * name = string
	 * multiple = 0|1
	 * id = unique string
	 *
	 * Parameter Defaults:
	 * name = file
	 * multiple = 0
	 * id = uniqid()
	 *
	 * @param $strReference
	 * @return string
	 */
	public function templateExtension($strReference){

		$strOut = '';
		$arrParams = array(
			'reference' => $strReference,
			'name' => 'file',
			'multiple' => 0,
			'id' => uniqid()
		);

		if(strstr($strReference,',')){

			$arrTempParams = explode(',',$strReference);
			$arrParams['reference'] = $arrTempParams[0];

			if(count($arrTempParams) == 2){
				$arrParams['name'] = $arrTempParams[1];
			}elseif(count($arrTempParams) == 3){
				$arrParams['name'] = $arrTempParams[1];
				$arrParams['multiple'] = $arrTempParams[2];
			}elseif(count($arrTempParams) == 4){
				$arrParams['name'] = $arrTempParams[1];
				$arrParams['multiple'] = $arrTempParams[2];
				$arrParams['id'] = $arrTempParams[3];
			}
		}

		switch($arrParams['reference']){

			case'upload':
			case'asset-upload':

				$arrTags = array(
					'uniqid' => $arrParams['id'],
					'name' => $arrParams['name'],
					'type' => ($arrParams['reference'] == 'asset-upload') ? 'asset' : 'file',
					'include-js' => (is_null(\Twist::Cache()->retrieve('asset-js-include'))) ? 1 : 0,
					'multiple' => ($arrParams['multiple'] == 1 || $arrParams['multiple'] == 'true') ? 1 : 0,
				);

				//Store a temp session for js output
				\Twist::Cache()->store('asset-js-include',1,0);

				$strOut = $this->resTemplate->build('upload.tpl',$arrTags);
				break;

			case'upload-html':
			case'asset-upload-html':

				$arrTags = array(
					'uniqid' => $arrParams['id'],
					'name' => $arrParams['name'],
					'type' => ($arrParams['reference'] == 'asset-upload-html') ? 'asset' : 'file',
					'include-js' => (is_null(\Twist::Cache()->retrieve('asset-js-include'))) ? 1 : 0,
				);

				//Store a temp session for js output
				\Twist::Cache()->store('asset-js-include',1,0);

				$strOut = $this->resTemplate->build('upload-html.tpl',$arrTags);
				break;

			case'upload-init':
			case'asset-upload-init':

				$arrTags = array(
					'uniqid' => $arrParams['id'],
					'name' => $arrParams['name'],
					'type' => ($arrParams['reference'] == 'asset-upload-init') ? 'asset' : 'file',
				);

				$strOut = $this->resTemplate->build('upload-init.tpl',$arrTags);
				break;

			case'upload-js':
			case'asset-upload-js':

				$strOut = '';

				if(is_null(\Twist::Cache()->retrieve('asset-js-include'))){
					$strOut = $this->resTemplate->build('upload-js.tpl');

					//Store a temp session for js output
					\Twist::Cache()->store('asset-js-include',1,0);
				}

				break;
		}

		return $strOut;
	}

}