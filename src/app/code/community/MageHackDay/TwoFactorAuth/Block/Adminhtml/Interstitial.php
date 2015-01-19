<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Interstitial extends Mage_Adminhtml_Block_Template
{
    /**
     * Check whether the user has secret question
     *
     * @return bool
     */
    public function hasSecretQuestion()
    {
        return Mage::getResourceModel('twofactorauth/user_question')->hasQuestions($this->getUser());
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }
}