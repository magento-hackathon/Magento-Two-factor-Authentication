<?php

/**
 * Adminhtml edit admin user account
 */
class MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Account_Edit extends Mage_Adminhtml_Block_System_Account_Edit
{
    public function __construct()
    {
        parent::__construct();

        if (Mage::getResourceModel('twofactorauth/user_cookie')->hasCookies(Mage::getSingleton('admin/session')->getUser()->getId())) {
            $this->addButton('clear_2fa_cookies', array(
                'label'     => Mage::helper('twofactorauth')->__('Clear 2FA Cookies'),
                'class'     => 'delete',
                'onclick'   => 'confirmSetLocation(\''
                    . Mage::helper('twofactorauth')->__('Are you sure you want to clear 2FA cookies?')
                    . '\', \'' . $this->getUrl('adminhtml/twofactorauth/clearCookies') . '\')'
            ));
        }
    }
}
