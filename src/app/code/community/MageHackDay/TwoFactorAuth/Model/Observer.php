<?php

class MageHackDay_TwoFactorAuth_Model_Observer {

    /**
     * Listens to the admin_user_authenticate_after Event and checks whether the user has access to areas that are configured
     * to be protected by Two Factor Auth. If so, send the user to either add a Two Factor Auth to their Account, or enter a
     * code from their connected Auth provider
     *
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
        $bTfaRequired = false;
        foreach($aProtectedResources as $vResourceId => $aProtectedResource){
            if(Mage::getSingleton('admin/session')->isAllowed($aProtectedResource['resource_id'])){
                $bTfaRequired = true;
                break;
            }
        }
        if($bTfaRequired){
            Mage::log('this user has ACLs for resources that we need to protect via TFA');
            $oResponse = Mage::app()->getResponse();
            if(!$user->getTwofactorToken()){
                Mage::log('User is missing required TFA secret');
                $vMessage = Mage::helper('twofactorauth')->__('Please connect your Two Factor Authentication before accessing restricted admin functionality');
                Mage::getSingleton('adminhtml/session')->addError($vMessage);
                Mage::getSingleton('admin/session')->setTfaRequired(true);
                $vRedirectUrl = Mage::helper("adminhtml")->getUrl("adminhtml/system_account/index");
            }
            else{
                $vRedirectUrl = Mage::helper("adminhtml")->getUrl("adminhtml/twofactorauth/interstitial");
            }
            $oResponse->setRedirect($vRedirectUrl);
            $oResponse->sendResponse();
            exit();
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
            $user = Mage::getSingleton('admin/session')->getUser();
            try{
                $user->setTwofactorToken($secret)->save();
                Mage::getSingleton('admin/session')->unsTfaRequired(true);
            }
            catch(Exception $e){
                Mage::logException($e);
            }
        }
        // Failure
        else {
            $message = Mage::helper('twofactorauth')->__('The code you entered was invalid.  Please try again.');
            Mage::getSingleton('adminhtml/session')->addError($message);
        }
    }

    /**
     * Listens for the adminhtml_controller_action_predispatch_start Event to
     * check if an Admin that was sent to My Account to associate a Two Factor Auth
     * is attempting to navigate away without saving the required secret for TFA
     *
     * @param $oObserver
     */
    public function checkTfaSubmitted($oObserver){
        $bTfaStillRequired = Mage::getSingleton('admin/session')->getTfaRequired();
        if($bTfaStillRequired && Mage::app()->getRequest()->getActionName() != 'logout'){
            $vMessage = Mage::helper('twofactorauth')->__('Please connect your Two Factor Authentication before accessing restricted admin functionality');
            Mage::getSingleton('adminhtml/session')->addError($vMessage);
            $vRedirectUrl = Mage::helper("adminhtml")->getUrl("adminhtml/system_account/index");
        }
    }

    public function customerAuthenticateAfter($observer)
    {
        $customer = $observer->getEvent()->getModel();

        if($customer->getTwofactorauthToken()) {
            $redirectUrl = Mage::getModel("core/url")->getUrl("twofactorauth/interstitial");
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect($redirectUrl)
                ->sendResponse();
        }
    }
}
