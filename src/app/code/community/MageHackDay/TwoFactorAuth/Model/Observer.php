<?php

class MageHackDay_TwoFactorAuth_Model_Observer {

	/*
	 * see authenticate function in Mage_Admin_Model_User
	 */	
	public function adminUserAuthenticateAfter($observer) {
		$event 		= $observer->getEvent();
		$username 	= $event->getUsername();
        /** @var $user Mage_Admin_Model_User */
		$user 		= $event->getUser();
        $oRole = $user->getRole();
        $aResources = $oRole->getResourcesList2D();
        $vSerializedProtectedResources = Mage::getStoreConfig('admin/security/twofactorauth_protected_resources');
        $aProtectedResources = unserialize($vSerializedProtectedResources);
        $aProtectedResourceIds = array();
        foreach($aProtectedResources as $vResourceId => $aProtectedResource){
            $aProtectedResourceIds[] = $aProtectedResource['resource_id'];
        }
        $aMatchingResources = array_intersect($aProtectedResourceIds, $aResources);
        if(count($aMatchingResources)>0){
            Mage::log('this user has ACLs for resources that we need to protect via TFA');
            $oResponse = Mage::app()->getResponse();
            $vRedirectUrl = Mage::helper("adminhtml")->getUrl("adminhtml/twofactorauth/interstitial");
            
            $oResponse->setRedirect($vRedirectUrl);
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
