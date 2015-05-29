<?php

class MageHackDay_TwoFactorAuth_Helper_Data extends Mage_Core_Helper_Data
{
    public function isActive()
    {
        return Mage::getStoreConfigFlag('admin/security/active');
    }

    public function isForceForBackend()
    {
        return Mage::getStoreConfigFlag('admin/security/force_for_backend');
    }

    public function isFrontendActive()
    {
        return Mage::getStoreConfigFlag('admin/security/frontend_active');
    }

    public function getCustomerLoginRedirectUrl() {
        return Mage::getUrl('twofactorauth/customer/configure');
    }
}