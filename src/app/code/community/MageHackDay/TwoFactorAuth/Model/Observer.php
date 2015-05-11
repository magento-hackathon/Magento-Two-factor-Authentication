<?php

class MageHackDay_TwoFactorAuth_Model_Observer
{
    public $_ignoreCalls = array();

    //if you need to extend the list of modules: rewrite this class and overwrite this function
    public function __construct()
    {
      $this->_ignoreCalls = array(
        'routers' => array(
            'customer',
            'twofactorauth'
            //do not add the following ones, if you enable them, the customer is just logged in without second factor!
            //'catalog', 'cms', 'checkout', ...
        ),
        'controllers' => array(
            'account',
            'interstitial'
        ),
        'actions' => array(
            'logout',
            'login',
            'index'
        ),
      );
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    private function _getAdminhtmlSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    private function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Listens to the admin_user_authenticate_after Event and checks whether the user has access to areas that are configured
     * to be protected by Two Factor Auth. If so, send the user to either add a Two Factor Auth to their Account, or enter a
     * code from their connected Auth provider
     *
     * @event admin_user_authenticate_after
     * @param Varien_Event_Observer $observer
     * @return MageHackDay_TwoFactorAuth_Model_Observer
     */
    public function adminUserAuthenticateAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('twofactorauth')->isActive()) {
            return $this;
        }

        $event = $observer->getEvent();
        $user = $event->getUser(); /** @var $user Mage_Admin_Model_User */
        if (Mage::helper('twofactorauth/auth')->isAuthorized($user)) {
            return $this;
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
            if (!$user->getTwofactorToken()) {
                $this->_getAdminhtmlSession()->setTfaNotAssociated(TRUE);
                $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/qr');
            } else {
                $this->_getAdminhtmlSession()->setTfaNotEntered(TRUE);
                $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/interstitial');
            }
            $oResponse->setRedirect($vRedirectUrl);
            $oResponse->sendResponse();
        }
        return $this;
    }

    /**
     * Listens for the controller_action_postdispatch_adminhtml Event to
     * check if an Admin that was sent to either:
     *   (a) My Account to associate a Two Factor Auth, or
     *   (b) interstitial page to enter their TFA value
     * is attempting to navigate away without performing the necessary TFA action
     *
     * @event controller_action_postdispatch_adminhtml
     * @param Varien_Event_Observer $observer
     * @return MageHackDay_TwoFactorAuth_Model_Observer
     */
    public function checkTfaSubmitted(Varien_Event_Observer $observer)
    {
        $request = $observer->getControllerAction()->getRequest();

        if ($request->getActionName() == 'logout' || !Mage::helper('twofactorauth')->isActive()) {
            return $this;
        }

        if ($request->getControllerName() == 'twofactorauth') {
            return $this;
        }

        $vRedirectUrl = '';
        if ($this->_getAdminhtmlSession()->getTfaNotAssociated()) {
            $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/qr');
        } else if ($this->_getAdminhtmlSession()->getTfaNotEntered()) {
            $vRedirectUrl = Mage::helper('adminhtml')->getUrl('adminhtml/twofactorauth/interstitial');
        }
        if ($vRedirectUrl) {
            $vAction = $request->getActionName();
            Mage::app()->getFrontController()->getAction()->setFlag($vAction, Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, TRUE);
            $oResponse = Mage::app()->getResponse();
            $oResponse->setRedirect($vRedirectUrl);
            $oResponse->sendResponse();
        }

        return $this;
    }

    /**
     * @event customer_customer_authenticated
     * @param Varien_Event_Observer $observer
     * @return MageHackDay_TwoFactorAuth_Model_Observer
     */
    public function customerAuthenticateAfter(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('twofactorauth')->isActive() || !Mage::helper('twofactorauth')->isFrontendActive()) {
            return $this;
        }
        $customer = $observer->getEvent()->getModel();

        if ($customer->getTwofactorauthToken()) {
            $redirectUrl = Mage::getModel('core/url')->getUrl('twofactorauth/interstitial');
            $session = $this->_getCustomerSession();
            $session->setOriginalAfterAuthUrl($session->getAfterAuthUrl());
            $session->setAfterAuthUrl($redirectUrl);
        }
        return $this;
    }

    /**
     * Listens for the controller_action_postdispatch Event to
     * check if Customer that was sent to either:
     *   (a) catalog/cms to surf on
     *   (b) interstitial page to enter their TFA value
     * is attempting to navigate away without performing the necessary TFA action
     *
     * @event controller_action_postdispatch
     * @param Varien_Event_Observer $observer
     * @return MageHackDay_TwoFactorAuth_Model_Observer
     */
    public function checkTfaSubmittedCustomer(Varien_Event_Observer $observer)
    {
      //do we actually care about the call?
      /**@var $request Mage_Core_Controller_Request_Http*/
      $request = $observer->getEvent()->getControllerAction()->getRequest();
      if (
          in_array($request->getRouteName(), $this->_ignoreCalls['routers']) &&
          in_array($request->getControllerName(), $this->_ignoreCalls['controllers']) &&
          in_array($request->getActionName(), $this->_ignoreCalls['actions'])
         )
      {
        return $this;
      }


      //for performance reasons: try to return on every step - i know it looks cluttered
      //achtive?
      if ( !Mage::helper('twofactorauth')->isActive() ||
          !Mage::helper('twofactorauth')->isFrontendActive() )
      {
        return $this;
      }

      //logged in?
      $session = Mage::getSingleton('customer/session');
      if ( !$session->isLoggedIn() )
      {
        return $this;
      }

      //do we really have a customer?
      $customer = $session->getCustomer();
      if ( !$customer || !$customer->getId() )
      {
        return $this;
      }

      //deactivated?
      $authToken = $customer->getTwofactorauthToken();
      if ( empty($authToken) )
      {
        return $this;
      }

      //get Marker from Session
      $TFAOK = $session->getData("2FA_OK");
      //if not set or value different from (bool) true > redirect to enter code
      if ( is_null($TFAOK) || $TFAOK !== true )
      {
        $session->addError( Mage::helper('twofactorauth')->__('You have enabled Two-Factor Authentication but not entered your code yet.') );
        $oResponse = Mage::app()->getResponse();
        $url = Mage::getModel('core/url')->getUrl('twofactorauth/interstitial');
        $oResponse->setRedirect( $url );
        $oResponse->sendResponse();
      }
      return $this;
    }
}
