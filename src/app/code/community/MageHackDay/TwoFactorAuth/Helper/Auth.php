<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Helper_Auth extends Mage_Core_Helper_Abstract
{

    /** @var PHPGangsta_GoogleAuthenticator */
    private $_authenticator;

    /**
     * @return PHPGangsta_GoogleAuthenticator
     */
    protected function _getAuth()
    {
        if (empty($this->_authenticator)) {
            $this->_authenticator = new PHPGangsta_GoogleAuthenticator();
        }

        return $this->_authenticator;
    }

    /**
     * Create a new 2fa secret
     *
     * @return string
     */
    public function createSecret()
    {
        return $this->_getAuth()->createSecret();
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
        return $this->_getAuth()->getQRCodeGoogleUrl($name, $secret);
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
        return $this->_getAuth()->verifyCode($secret, $code, 2); // 2 = 60 seconds
    }
}