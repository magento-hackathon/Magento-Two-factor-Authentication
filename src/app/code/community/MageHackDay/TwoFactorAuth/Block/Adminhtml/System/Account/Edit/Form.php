<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Account_Edit_Form
    extends Mage_Adminhtml_Block_System_Account_Edit_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        if(!Mage::helper('twofactorauth')->isActive()) {
            return $this;
        }

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')
            ->load($userId);


        $authHelper = Mage::helper('twofactorauth/auth');

        $session = Mage::getSingleton('core/session');

        // Create a new secret for each new session
        $secret = $session->getSecret();
        $qrCodeUrl = $session->getQrCodeUrl();
        if(!$secret || !$qrCodeUrl) {
            $secret = $authHelper->createSecret();
            $session->setSecret($secret);
            $qrCodeUrl = $authHelper->getQrCodeImageUrl($authHelper->getStoreName(), $secret);
            $session->setQrCodeUrl($qrCodeUrl);
        }

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

        $helpMsg = Mage::helper('twofactorauth')->__('Scan the code above into Google Authenticator, then enter the code here and click save');
        $afterElementHtml = '<p class="nm"><small>' . $helpMsg . '</small></p>';

        $fieldset->addField('twofactorauth_code', 'text', array(
                'name'  => 'twofactorauth_code',
                'label' => Mage::helper('twofactorauth')->__('Code'),
                'title' => Mage::helper('twofactorauth')->__('Code'),
                'after_element_html' => $afterElementHtml
            )
        );

        $this->setForm($form);
    }
}