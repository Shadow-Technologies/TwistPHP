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

namespace Twist\Core\Models\User;

use Twist\Core\Models\Protect\Firewall;

class Auth{

	protected static $blValidated = false;
	protected static $objSessionHandler = null;
	protected static $arrCurrentSession = array(
		'status' => false,
		'issue' => '',
		'message' => '',
		'diagnosis' => '',
		'session_key' => null,
		'user_id' => null,
		'user_uid' => null,
		'user_data' => array()
	);

	/**
	 * Hash a password
	 * @param string $strPassword The password to hash
	 * @return bool|string
	 */
	public static function hashPassword($strPassword) {
		$intCost = (!is_null(\Twist::framework()->setting('USER_AUTH_HASH_COST'))) ? \Twist::framework()->setting('USER_AUTH_HASH_COST') : 12;
		return password_hash( $strPassword, PASSWORD_BCRYPT, array( 'cost' => $intCost ) );
	}

	/**
	 * Legacy password has for backwards comparability, the hash algorithm used is defined in the USER_AUTH_LEGACY_HASH setting
	 * @param string $strPassword The password to hash
	 * @return bool|string
	 */
	public static function legacyPassword($strPassword) {
		$strAlgorithm = (!is_null(\Twist::framework()->setting('USER_AUTH_LEGACY_HASH'))) ? \Twist::framework()->setting('USER_AUTH_LEGACY_HASH') : 'sha1';
		return hash($strAlgorithm,$strPassword);
	}

	public static function current($blUpdateKey = true){

		if(self::$arrCurrentSession['status'] === false && self::$blValidated === false){

			//Get the PHP session object
			$objSession = \Twist::Session();

			$intUserID = 0;
			$strSessionKey = $objSession->data('user-session_key');

			//Validate the session if available else validate the cookie if remembered
			if(!is_null($strSessionKey)){
				$intUserID = self::SessionHandler()->validateCode($strSessionKey,$blUpdateKey);
			}elseif(self::SessionHandler()->remembered()){
				$intUserID = self::SessionHandler()->validateCookie($blUpdateKey);
				$strSessionKey = (array_key_exists('remember',$_COOKIE)) ?  $_COOKIE['remember'] : \Twist::Session()->data('user-session_key');
			}

			//Rebuild the users auth array if is a valid user
			if($intUserID > 0){

				if(is_null($objSession->data('user-id'))){

					$arrUserData = \Twist::Database(\Twist::userDatabase())->records(TWIST_DATABASE_TABLE_PREFIX.'users')->get($intUserID,'id')->values();

					$objSession->data('user-id',$intUserID);
					$objSession->data('user-level',$arrUserData['level']);
					$objSession->data('user-email',$arrUserData['email']);
					$objSession->data('user-enabled',$arrUserData['enabled']);
					$objSession->data('user-verified',$arrUserData['verified']);
					$objSession->data('user-temp_password',$arrUserData['temp_password']);
					$objSession->data('user-session_key',$strSessionKey);
					$objSession->data('user-logged_in',\Twist::DateTime()->time());

					//Set the users name into the php session
					$objSession->data('user-name',sprintf('%s %s',$arrUserData['firstname'],$arrUserData['surname']));
					$objSession->data('user-firstname',$arrUserData['firstname']);
					$objSession->data('user-surname',$arrUserData['surname']);
				}

				$resResult = \Twist::Database(\Twist::userDatabase())->query("SELECT * FROM `%suser_groups` WHERE `id` IN (SELECT `group_id` FROM `%suser_group_members` WHERE `user_id` = %d)",TWIST_DATABASE_TABLE_PREFIX,TWIST_DATABASE_TABLE_PREFIX,$intUserID);
				$arrUserGroups = $resResult->rows();
				$objSession->data('user-groups',$arrUserGroups);

				self::$arrCurrentSession['status'] = true;
				self::$arrCurrentSession['session_key'] = $strSessionKey;
				self::$arrCurrentSession['user_id'] = $intUserID;
				self::$arrCurrentSession['user_uid'] = $intUserID;
				self::$arrCurrentSession['user_data'] = array(
					'id' => $objSession->data('user-id'),
					'uid' => $objSession->data('user-uid'),
					'enabled' => $objSession->data('user-id'),
					'verified' => $objSession->data('user-id'),
					'level' => $objSession->data('user-level'),
					'groups' => $objSession->data('user-groups'),
					'temp_password' => $objSession->data('user-temp_password'),
					'firstname' => $objSession->data('user-firstname'),
					'surname' => $objSession->data('user-surname'),
					'email' => $objSession->data('user-email')
				);

				//Set shutdown function to log activity and IP address upon script shutdown
				\Twist::framework()->register()->shutdownEvent('auth-user-lastactive','Twist\Core\Models\User\Auth','logLastActive');
			}

			//Tell the script not to try an recheck current session
			self::$blValidated = true;
		}

		return self::$arrCurrentSession;
	}

	/**
	 * Log the user in and generate an active session (Stores session data into the browser)
	 * @param $strEmail
	 * @param $strPassword
	 * @param bool $blRememberMeCookie
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function login($strEmail,$strPassword,$blRememberMeCookie = false){

		self::validate($strEmail,$strPassword);
		self::startSession($blRememberMeCookie);

		return self::$arrCurrentSession;
	}

	/** Generate the users session to keep them logged in on every page load
	 * @param bool $blRememberMeCookie
	 */
	public static function startSession($blRememberMeCookie = false){

		if(self::$arrCurrentSession['status']){

			//Create the session key or session cookie
			if($blRememberMeCookie){
				self::$arrCurrentSession['session_key'] = self::SessionHandler()->createCookie(self::$arrCurrentSession['user_id']);
			}else{
				self::$arrCurrentSession['session_key'] = self::SessionHandler()->createCode(self::$arrCurrentSession['user_id']);
			}

			//Get the PHP session object
			$objSession = \Twist::Session();

			$objSession->data('user-id',self::$arrCurrentSession['user_id']);
			$objSession->data('user-uid',self::$arrCurrentSession['user_uid']);
			$objSession->data('user-level',self::$arrCurrentSession['user_data']['level']);
			$objSession->data('user-groups',self::$arrCurrentSession['user_data']['groups']);
			$objSession->data('user-email',self::$arrCurrentSession['user_data']['email']);
			$objSession->data('user-enabled',self::$arrCurrentSession['user_data']['enabled']);
			$objSession->data('user-verified',self::$arrCurrentSession['user_data']['verified']);
			$objSession->data('user-temp_password',self::$arrCurrentSession['user_data']['temp_password']);
			$objSession->data('user-session_key',self::$arrCurrentSession['session_key']);
			$objSession->data('user-logged_in',\Twist::DateTime()->time());

			//Set the users name into the php session
			$objSession->data('user-name',sprintf('%s %s',self::$arrCurrentSession['user_data']['firstname'],self::$arrCurrentSession['user_data']['surname']));
			$objSession->data('user-firstname',self::$arrCurrentSession['user_data']['firstname']);
			$objSession->data('user-surname',self::$arrCurrentSession['user_data']['surname']);

			//Set shutdown function to log activity and IP address upon script shutdown
			\Twist::framework()->register()->shutdownEvent('auth-user-lastlogin','Twist\Core\Models\User\Auth','logLastLogin');
		}
	}

	/**
	 * Validate a users credentials without logging the user into the system
	 * @param $strEmail
	 * @param $strPassword
	 * @param bool $blBypassPassword
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function validate($strEmail,$strPassword,$blBypassPassword = false){

		//If the user is still not valid then check email and password
		if(!is_null($strEmail) && !is_null($strPassword)){

			$resUser = \Twist::Database(\Twist::userDatabase())->records(TWIST_DATABASE_TABLE_PREFIX.'users')->get($strEmail,'email');

			if( !is_null( $resUser ) ) {
				$arrUserData = $resUser->values();
				if( array_key_exists( 'id', $arrUserData ) ){

					$blValidPassword = false;

					if($blBypassPassword === true){
						$blValidPassword = true;
					}else{

						if( password_verify( $strPassword, $arrUserData['password'] ) ) {
							$blValidPassword = true;
						} else if( $arrUserData['password'] === self::legacyPassword( $strPassword ) ) {
							$blValidPassword = true;
							$strPasswordHash = self::hashPassword($strPassword);
							$resUser->set( 'password', $strPasswordHash );
							$resUser->commit();
						}
					}

					if( $blValidPassword ) {
						if( $arrUserData['enabled'] === '1' ) {
							if( \Twist::framework()->setting( 'USER_EMAIL_VERIFICATION' ) === false || ( \Twist::framework()->setting( 'USER_EMAIL_VERIFICATION' ) && $arrUserData['verified'] === '1' ) ) {

								//We don't want to store the users hashed password in the auth array
								unset( $arrUserData['password'] );

								//But we do want to save the email address (don't return it in the SQL as it is more secure not to return both email and password in at together)
								$arrUserData['email'] = $strEmail;

								$resResult = \Twist::Database(\Twist::userDatabase())->query("SELECT * FROM `%suser_groups` WHERE `id` IN (SELECT `group_id` FROM `%suser_group_members` WHERE `user_id` = %d)",TWIST_DATABASE_TABLE_PREFIX,TWIST_DATABASE_TABLE_PREFIX,$arrUserData['id']);
								$arrUserData['groups'] = $resResult->rows();

								self::$arrCurrentSession['status'] = true;
								self::$arrCurrentSession['user_id'] = $arrUserData['id'];
								self::$arrCurrentSession['user_uid'] = $arrUserData['uid'];
								self::$arrCurrentSession['user_data'] = $arrUserData;

								if(\Twist::framework()->setting( 'USER_PASSWORD_CHANGE' ) === true && $arrUserData['temp_password'] === '1'){
									//The user is on a temporary password and a change is required by the system
									self::$arrCurrentSession['issue'] = 'temporary';
									self::$arrCurrentSession['message'] = 'You are using a temporary password, please change your password';
									self::$arrCurrentSession['diagnosis'] = 'The account is running on a temporary password and needs to be reset';
								}
							} else {
								self::$arrCurrentSession['issue'] = 'verify';
								self::$arrCurrentSession['message'] = 'You have not verified your email address';
								self::$arrCurrentSession['diagnosis'] = 'The account has not been verified';
							}
						} else {
							self::$arrCurrentSession['issue'] = 'disabled';
							self::$arrCurrentSession['message'] = 'Your account has been disabled';
							self::$arrCurrentSession['diagnosis'] = 'The account has been set to disabled';
						}
					} else {
						self::$arrCurrentSession['issue'] = 'password';
						self::$arrCurrentSession['message'] = 'Invalid login credentials, please try again';
						self::$arrCurrentSession['diagnosis'] = 'Password does not match that of the requested account';
					}
				} else {
					self::$arrCurrentSession['issue'] = 'email';
					self::$arrCurrentSession['message'] = 'Invalid login credentials, please try again';
					self::$arrCurrentSession['diagnosis'] = 'Email address not registered to a user';
				}
			} else {
				self::$arrCurrentSession['issue'] = 'email';
				self::$arrCurrentSession['message'] = 'Invalid login credentials, please try again';
				self::$arrCurrentSession['diagnosis'] = 'Email address not registered to a user';
			}

			//Disabled accounts and temporary passwords are not login failures, however unverified accounts are. If you are
			//repeatedly asked to verify your email address and you dont maybe a fake email has been used
			if(in_array(self::$arrCurrentSession['issue'],array('temporary','disabled')) || self::$arrCurrentSession['status']){
				Firewall::successLogin();
			}else{
				$arrWatchStats = Firewall::failedLogin();

				if(is_array($arrWatchStats)){
					self::$arrCurrentSession['message'] .= '. You have '.($arrWatchStats['limit'] - $arrWatchStats['attempt']).' attempts remaining';
					self::$arrCurrentSession['diagnosis'] .= '. '.($arrWatchStats['limit'] - $arrWatchStats['attempt']).' login attempts remaining until ban';
				}
			}
		}

		return self::$arrCurrentSession;
	}

	/**
	 * Log the user out of the system
	 * @return bool
	 */
	public static function logout(){

		self::SessionHandler()->forget();
		\Twist::Session()->remove();

		self::$blValidated = false;

		self::$arrCurrentSession = array(
			'status' => false,
			'issue' => '',
			'message' => '',
			'diagnosis' => '',
			'session_key' => null,
			'user_id' => null,
			'user_data' => array()
		);

		return true;
	}

	/**
	 * Log the last time the user was active, by default this is called as a PHP shutdown function for users that are logged in
	 */
	public static function logLastActive(){

		if(!is_null(self::$arrCurrentSession['user_id'])){

			$resUser = \Twist::User()->get(self::$arrCurrentSession['user_id']);
			$resUser->lastActive();
			$resUser->commit();
		}
	}

	/**
	 * Log the time and IP address of the user upon las login, by default this is called as a PHP shutdown function for users that are logged in
	 */
	public static function logLastLogin(){

		if(!is_null(self::$arrCurrentSession['user_id'])){

			$resUser = \Twist::User()->get(self::$arrCurrentSession['user_id']);
			$resUser->lastLogin($_SERVER['REMOTE_ADDR']);
			$resUser->commit();
		}
	}

	/**
	 * Get an instance of the user session handler
	 * @return \Twist\Core\Models\User\SessionHandler
	 */
	public static function SessionHandler(){

		if(is_null(self::$objSessionHandler)){
			self::$objSessionHandler = new SessionHandler();

			$intSessionLife = \Twist::framework()->setting('USER_REMEMBER_LENGTH');

			//Set the remember me life span in seconds
			if($intSessionLife > 0){
				self::$objSessionHandler->setSessionLife(($intSessionLife * 60) * 60);
			}
		}

		return self::$objSessionHandler;
	}
}
