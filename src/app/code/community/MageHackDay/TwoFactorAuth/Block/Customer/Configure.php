<?php

class MageHackDay_TwoFactorAuth_Block_Customer_Configure extends Mage_Core_Block_Template
{
    protected $_secret = null;

    /**
     * Get the form save action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Get the form reset action
     *
     * @return string
     */
    public function getResetAction() {
        return $this->getUrl('*/*/reset');
    }

    /**
     * Is 2fa enabled for the currently logged in user?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (Mage::getSingleton('customer/session')->getCustomer()->getTwofactorauthToken());
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        if (!$this->_secret) {
            $authHelper = Mage::helper('twofactorauth/auth');
            $this->_secret = $authHelper->createSecret();
        }

        return $this->_secret;
    }

    /**
     * Get the url of an image for configuring a new 2fa client
     *
     * @return string
     */
    public function getQrCodeImageUrl()
    {
        $authHelper = Mage::helper('twofactorauth/auth');

        return $authHelper->getQrCodeImageUrl($authHelper->getStoreName(), $this->getSecret());
    }
}