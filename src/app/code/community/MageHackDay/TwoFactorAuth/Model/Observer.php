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

    public function verifySecret($observer)
    {
        $authHelper = Mage::helper('twofactorauth/auth');

        $code = Mage::app()->getRequest()->getParam('twofactorauth_code');
        $secret = Mage::app()->getRequest()->getParam('twofactorauth_secret');

        // The user didn't enter a code so they aren't try to configure 2fa
        if (!$code) {
            return;
        }

        // Success
        if ($authHelper->verifyCode($code, $secret)) {
            $userId = Mage::getSingleton('admin/session')->getUser()->getId();
            $user = Mage::getModel('admin/user')
                ->load($userId);

            $user->setTwofactorToken($secret)
                ->save();
        }
        // Failure
        else {
            $message = Mage::helper('twofactorauth')->__('The code you entered was invalid.  Please try again.');
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
    }
}