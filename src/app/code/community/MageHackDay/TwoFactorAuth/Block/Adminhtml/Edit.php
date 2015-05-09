<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'twofactorauth';
        $this->_removeButton('delete');

        $reAuthenticated = Mage::helper('twofactorauth/auth')->isReAuthenticated();
        $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */

        if (Mage::getResourceModel('twofactorauth/user_cookie')->hasCookies($user->getId()) && $reAuthenticated) {
            $this->addButton('clear_2fa_cookies', array(
                'label'     => Mage::helper('twofactorauth')->__('Force security code on next login'),
                'onclick'   => 'confirmSetLocation(\''
                    . Mage::helper('twofactorauth')->__('Are you sure you want to force security code on next login?')
                    . '\', \'' . $this->getUrl('adminhtml/twofactorauth/clearCookies') . '\')'
            ));
        }

        if ($reAuthenticated) {
            $this->addButton('reset_2fa', array(
                'label'     => Mage::helper('twofactorauth')->__('Reset authentication completely'),
                'onclick'   => 'confirmSetLocation(\''
                    . Mage::helper('twofactorauth')->__('Are you sure you want to reset authentication completely?')
                    . '\', \'' . $this->getUrl('adminhtml/twofactorauth/reset') . '\')'
            ));
        }

        if ( ! $reAuthenticated) {
            $this->updateButton('save', 'label', $this->__('Submit Password'));
        }
    }

    public function getHeaderText()
    {
        return Mage::helper('twofactorauth')->__('Two-Factor Authentication Setup');
    }
}
