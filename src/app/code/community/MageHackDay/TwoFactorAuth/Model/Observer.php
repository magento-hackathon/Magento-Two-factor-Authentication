<?php

class MageHackDay_TwoFactorAuth_Model_Observer
{
    /**
     * Listens to the admin_user_authenticate_after Event and checks whether the user has access to areas that are configured
     * to be protected by Two Factor Auth. If so, send the user to either add a Two Factor Auth to their Account, or enter a
     * code from their connected Auth provider
     */
    public function adminUserAuthenticateAfter($observer)
    {
        if ( ! Mage::helper('twofactorauth')->isActive()) {
            return;
        }

        $event = $observer->getEvent();
        $user = $event->getUser(); /** @var $user Mage_Admin_Model_User */
        if (Mage::helper('twofactorauth/auth')->isAuthorized($user)) {
            return;
        }

        /*
         * If 2FA is not forced for all backend users, check if user has access to protected resources.
         * If user has no access to protected resources, 2FA authentication is not necessary
         */
        $bTfaRequired = FALSE;
        if (Mage::helper('twofactorauth')->isForceForBackend()) {
            $bTfaRequired = TRUE;
        } else {
            $aProtectedResources = explode(',',Mage::getStoreConfig('admin/security/twofactorauth_protected_resources'));
            foreach ($aProtectedResources as $aProtectedResource) {
                if (Mage::getSingleton('admin/session')->isAllowed($aProtectedResource)) {
                    $bTfaRequired = TRUE;
                    break;
                }
            }
        }

        if ($bTfaRequired) {
            $oResponse = Mage::app()->getResponse();
            if ( ! $user->getTwofactorToken()) {
                $this->getAdminhtmlSession()->setTfaNotAssociated(TRUE);
                $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/qr');
            } else {
                $this->getAdminhtmlSession()->setTfaNotEntered(TRUE);
                $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/interstitial');
            }
            $oResponse->setRedirect($vRedirectUrl);
            $oResponse->sendResponse();
            return;
        }
    }

    /**
     * Listens for the controller_action_postdispatch_adminhtml Event to
     * check if an Admin that was sent to either:
     *   (a) My Account to associate a Two Factor Auth, or
     *   (b) interstitial page to enter their TFA value
     * is attempting to navigate away without performing the necessary TFA action
     *
     * @param $oObserver
     */
    public function checkTfaSubmitted($oObserver)
    {
        if (Mage::app()->getRequest()->getActionName() == 'logout' || ! Mage::helper('twofactorauth')->isActive()) {
            return $this;
        }

        $request = $oObserver->getControllerAction()->getRequest();
        if ($request->getControllerName() == 'twofactorauth') {
            return $this;
        }

        $vRedirectUrl = '';
        if ($this->getAdminhtmlSession()->getTfaNotAssociated()) {
            $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/qr');
        } else if ($this->getAdminhtmlSession()->getTfaNotEntered()) {
            $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/interstitial');
        }
        if ($vRedirectUrl) {
            $oRequest = Mage::app()->getRequest();
            $vAction = $oRequest->getActionName();
            Mage::app()->getFrontController()->getAction()->setFlag($vAction, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, TRUE);
            $oResponse = Mage::app()->getResponse();
            $oResponse->setRedirect($vRedirectUrl);
            $oResponse->sendResponse();
        }
    }

    /**
     * @todo Store the original after auth url so we can redirect the user after entering their 2fa code
     *
     * @param $observer
     */
    public function customerAuthenticateAfter($observer)
    {
        if ( ! Mage::helper('twofactorauth')->isActive() || ! Mage::helper('twofactorauth')->isFrontendActive()) {
            return $this;
        }
        $customer = $observer->getEvent()->getModel();

        if ($customer->getTwofactorauthToken()) {
            $redirectUrl = Mage::getModel('core/url')->getUrl('twofactorauth/interstitial');
            $session = $this->getCustomerSession();
            $session->setOriginalAfterAuthUrl($session->getAfterAuthUrl());
            $session->setAfterAuthUrl($redirectUrl);
        }
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    public function getAdminhtmlSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
