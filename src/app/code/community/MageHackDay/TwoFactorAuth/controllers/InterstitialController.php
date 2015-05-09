<?php

class MageHackDay_TwoFactorAuth_InterstitialController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Display the interstitial form
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Verify a 2fa code
     */
    public function verifyAction()
    {
        $authHelper = Mage::helper('twofactorauth/auth');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $code = $this->getRequest()->getParam('code');

        // The code was invalid, redirect the user back to the interstitial
        if (!$authHelper->verifyCode($code, $customer->getTwofactorauthToken())) {
            Mage::getSingleton('core/session')->addError('The code you entered was invalid.  Please try again.');
            $this->_redirect('twofactorauth/interstitial');
            return;
        }

        // Redirect the user to their original destination
        $session = $this->_getSession();

        if (!$redirectUrl = $session->getOriginalAfterAuthUrl()) {
            $redirectUrl = Mage::getModel('core/url')->getUrl('customer/account');
        }

        $this->_redirectUrl($redirectUrl);
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}