<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Question extends Mage_Adminhtml_Block_Template
{
    /**
     * Get random secret question
     *
     * @return MageHackDay_TwoFactorAuth_Model_User_Question
     */
    public function getSecretQuestion()
    {
        return Mage::getResourceModel('twofactorauth/user_question_collection')
            ->addUserFilter($this->getUser())
            ->setRandomOrder()
            ->setCurPage(1)
            ->setPageSize(1)
            ->getFirstItem();
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }
}