<?php

require_once (Mage::getBaseDir('lib') . DS . 'ga4php' . DS . 'lib' . DS . 'ga4php.php');

class MageHackDay_TwoFactorAuth_Model_Authenticator extends GoogleAuthenticator {
	
	/*
	 * HOTP - counter based
	 * TOTP - time based
	 */
	public function getToken($username,$tokentype = "TOTP") {
		$token = $this->setUser($username, $tokentype);
		Mage::log("token = ".var_export($token,true));
		
		$user = Mage::getModel('admin/user')->loadByUsername($username);
		$user->setTwofactorauthToken($token);
		//$user->save(); //password gets messed up after saving?!
	}
	
	/*
	 * abstract function in GoogleAuthenticator, needs to be defined here
	 */
	function getData($username) {
		$user = Mage::getModel('admin/user')->loadByUsername($username);
		return $user->getTwofactorauthToken()==null ? false : $user->getTwofactorauthToken();
	}
	
	/*
	 * abstract function in GoogleAuthenticator, needs to be defined here
	 */
	function putData($username, $data) {
		$user = Mage::getModel('admin/user')->loadByUsername($username);
		$user->setTwofactorauthToken("test");
		$user->save();
	}
	
	/*
	 * abstract function in GoogleAuthenticator, needs to be defined here
	 */
	function getUsers() {
	}
}
	