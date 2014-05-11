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
        $this
            ->_title($this->__('Two Factor Authentication'))
            ->loadLayout()
            ->renderLayout()
        ;
        echo 'user should enter their auth string here @aedmonds';
    }

    public function verifyAction(){
        $oRequest = Mage::app()->getRequest();
        $vInputCode = $oRequest->getPost('input_code',null);
        $authHelper = Mage::helper('twofactorauth/auth');
        $oUser = Mage::getSingleton('admin/session')->getUser();
        $vSecret = $oUser->getTwofactorToken();
        if(!$vSecret){
            //user is accessing protected route without configured TFA
            return $this;
        }
        $bValid = $authHelper->verifyCode($vInputCode, $vSecret);
        if($bValid === false){
            Mage::getSingleton('admin/session')->addError('Two Factor Authentication has failed. Please try again or contact ');
        }
    }

}