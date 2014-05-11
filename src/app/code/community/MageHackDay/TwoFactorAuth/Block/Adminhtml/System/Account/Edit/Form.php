<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Account_Edit_Form
    extends Mage_Adminhtml_Block_System_Account_Edit_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')
            ->load($userId);

        // Create a new secret each page load, even if it's not being used
        $authHelper = Mage::helper('twofactorauth/auth');
        $secret = $authHelper->createSecret();
        $qrCodeUrl = $authHelper->getQrCodeImageUrl('Magento', $secret);

        $form = $this->getForm();

        $fieldset = $form->addFieldset(
            'twofactorauth',
            array('legend' => Mage::helper('adminhtml')->__('Two Factor Authentication'))
        );

        $fieldset->addField('twofactorauth_secret', 'hidden', array(
            'name' => 'twofactorauth_secret',
            'value' => $secret
        ));

        $fieldset->addField('twofactorauth_configured', 'label', array(
                'name'  => 'twofactorauth_configured',
                'label' => Mage::helper('twofactorauth')->__('Configured'),
                'title' => Mage::helper('twofactorauth')->__('Configured'),
                'value' => ($user->getTwofactorToken()) ? Mage::helper('twofactorauth')->__('Yes') : Mage::helper('twofactorauth')->__('No')
            )
        );

        $fieldset->addField('twofactor_token', 'label', array(
                'name'  => 'twofactor_token',
                'label' => Mage::helper('twofactorauth')->__('Secret Key'),
                'title' => Mage::helper('twofactorauth')->__('Secret Key'),
                'after_element_html' => "<img src=\"$qrCodeUrl\" />"
            )
        );

        $fieldset->addField('twofactorauth_code', 'text', array(
                'name'  => 'twofactorauth_code',
                'label' => Mage::helper('twofactorauth')->__('Code'),
                'title' => Mage::helper('twofactorauth')->__('Code')
            )
        );

        $this->setForm($form);
    }
}