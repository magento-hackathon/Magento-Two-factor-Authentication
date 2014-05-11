<?php

/**
 * adminhtml controller to enforce Two Factor Authentication
 *
 * @category    MageHackDay
 * @package     MageHackDay_TwoFactorAuth
 * @author      Jonathan Day <jonathan@aligent.com.au>
 */
class MageHackDay_TwoFactorAuth_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action{


    public function interstitialAction(){
        $this
            ->_title($this->__('Two Factor Authentication'))
//            ->loadLayout()
//            ->renderLayout()
        ;
        echo 'user should enter their auth string here @aedmonds';
        return $this;
    }

    public function verifyAction(){
        $oRequest = Mage::app()->getRequest();
        $vInputCode = $oRequest->getPost('input_code',null);
        // $ga->verifyCode($secret, $code, 2);
    }

}