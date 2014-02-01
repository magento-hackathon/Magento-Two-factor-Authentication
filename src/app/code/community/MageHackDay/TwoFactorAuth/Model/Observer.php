<?php

class MageHackDay_TwoFactorAuth_Model_Observer {

	/*
	 * see authenticate function in Mage_Admin_Model_User
	 */	
	public function adminUserAuthenticateAfter($observer) {
		$event 		= $observer->getEvent();
		$username 	= $event->getUsername();
		$user 		= $event->getUser();
		
		if($user->getId()) {
			/* is two factor authentication activated for this admin user */
			if($user->getData("twofactorauth")) {
				Mage::log("*** doTwoFactorAuth");
				
				$auth = Mage::getModel('twofactorauth/authenticator');
				$auth->getToken($username);
				
				//process with check
			}
		}
		
		return $this;
	}
}
