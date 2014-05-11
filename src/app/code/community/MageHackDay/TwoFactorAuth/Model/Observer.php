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
        $aResources = $oRole->getResourcesList();
        $vSerializedProtectedResources = Mage::getStoreConfig('admin/security/twofactorauth_protected_resources');
        $aProtectedResources = unserialize($vSerializedProtectedResources);
        foreach($aProtectedResources as $vProtectedResourceName){
            if(array_key_exists($vProtectedResourceName,$aResources)){
                Mage::log('this user has ACLs for resources that we need to protect via TFA');
                $oResponse = Mage::app()->getResponse();
                $vRedirectUrl = Mage::helper("adminhtml")->getUrl("twofactor/index/interstitial");
                $oResponse->setRedirect($vRedirectUrl);
            }
        }
		return $this;
	}
}
