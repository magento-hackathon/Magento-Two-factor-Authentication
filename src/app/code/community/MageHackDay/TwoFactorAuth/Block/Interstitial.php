<?php

class MageHackDay_TwoFactorAuth_Block_Interstitial extends Mage_Core_Block_Template
{
    public function getSaveUrl()
    {
        return $this->getUrl('twofactorauth/interstitial/verify');
    }
}
