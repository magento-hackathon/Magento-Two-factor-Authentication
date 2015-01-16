<?php

/**
 * adminhtml controller to enforce Two Factor Authentication
 *
 * @category    MageHackDay
 * @package     MageHackDay_TwoFactorAuth
 * @author      Jonathan Day <jonathan@aligent.com.au>
 */
class MageHackDay_TwoFactorAuth_Adminhtml_TwofactorauthController extends Mage_Adminhtml_Controller_Action{


    public function interstitialAction(){
        $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */
        if (Mage::helper('twofactorauth/auth')->isAuthorized($user)) {
            Mage::getSingleton('adminhtml/session')->unsTfaNotEntered(true);
            $this->_redirect('*');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function verifyAction(){
        $oRequest = Mage::app()->getRequest();
        $vInputCode = $oRequest->getPost('input_code',null);
        $rememberMe = (bool) $oRequest->getPost('remember_me', false);
        $authHelper = Mage::helper('twofactorauth/auth');
        $oUser = Mage::getSingleton('admin/session')->getUser(); /** @var $oUser Mage_Admin_Model_User */
        $vSecret = $oUser->getTwofactorToken();
        if(!$vSecret){
            //user is accessing protected route without configured TFA
            return $this;
        }
        $bValid = $authHelper->verifyCode($vInputCode, $vSecret);
        if($bValid === false){
            Mage::getSingleton('adminhtml/session')->addError('Two Factor Authentication has failed. Please try again or contact an administrator');
            $this->_redirect('adminhtml/twofactorauth/interstitial');
            return $this;
        }
        if ($rememberMe) {
            try {
                $cookie = $authHelper->generateCookie();
                Mage::getResourceModel('twofactorauth/user_cookie')->saveCookie($oUser->getId(), $cookie);
                $authHelper->setCookie($cookie);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        Mage::getSingleton('adminhtml/session')->unsTfaNotEntered(true);
        $this->_redirect('*');
        return $this;
    }

}