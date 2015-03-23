<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Qr extends Mage_Adminhtml_Block_Template
{
    /**
     * Retrieve QR code url
     *
     * @return string
     */
    public function getQrCodeUrl()
    {
        $qrCodeUrl = Mage::getSingleton('core/session')->getQrCodeUrl();
        if ( ! $qrCodeUrl) {
            $authHelper = Mage::helper('twofactorauth/auth');
            $qrCodeUrl = $authHelper->getQrCodeImageUrl($authHelper->getStoreName(), $this->getQrCodeSecret());
            Mage::getSingleton('core/session')->setQrCodeUrl($qrCodeUrl);
        }
        return $qrCodeUrl;
    }

    /**
     * Retrieve QR core secret
     *
     * @return string
     */
    public function getQrCodeSecret()
    {
        $secret = Mage::getSingleton('core/session')->getSecret();
        if ( ! $secret) {
            $secret = Mage::helper('twofactorauth/auth')->createSecret();
            Mage::getSingleton('core/session')->setSecret($secret);
        }
        return $secret;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }

}
