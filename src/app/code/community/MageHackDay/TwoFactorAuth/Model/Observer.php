<?php

class MageHackDay_TwoFactorAuth_Model_Observer {

	/*
	 * see authenticate function in Mage_Admin_Model_User
	 */	
	public function adminUserAuthenticateAfter($observer) {
		$event 		= $observer->getEvent();
		$username 	= $event->getUsername();
		$user 		= $event->getUser();
		$result		= $event->getResult();
		
		if($result) {
			if($user->getData("twofactorauth")) {
				Mage::log("doTwoFactorAuth2");
				
				//Two Factor Authentication not successfull
				//$result = false;
				$observer->getEvent()->setData('result', false);
				//Mage::throwException(Mage::helper('twofactorauth')->__('Two Factor Authentication not successfull'));
			}
		}
		
		return $observer;
	}
}
