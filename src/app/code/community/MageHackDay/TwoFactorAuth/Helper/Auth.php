<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Helper_Auth extends Mage_Core_Helper_Abstract
{
    /**
     * Create a new 2fa secret
     *
     * @return string
     */
    public function createSecret()
    {
        $ga = new PHPGangsta_GoogleAuthenticator();

        return $ga->createSecret();
    }

    /**
     * Get the image url to a new QR code
     *
     * @param string $name
     * @param string $secret
     * @return string
     */
    public function getQrCodeImageUrl($name, $secret)
    {
        $ga = new PHPGangsta_GoogleAuthenticator();

        return $ga->getQRCodeGoogleUrl($name, $secret);
    }

    /**
     * Verify a 2fa code
     *
     * @param int $code
     * @param string $secret
     * @return bool
     */
    public function verifyCode($code, $secret)
    {
        $ga = new PHPGangsta_GoogleAuthenticator();

        return $ga->verifyCode($secret, $code, 2); // 2 = 30 seconds
    }
}