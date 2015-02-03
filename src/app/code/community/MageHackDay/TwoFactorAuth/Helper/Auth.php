<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Helper_Auth extends Mage_Core_Helper_Abstract
{
    const TWO_FACTOR_AUTH_COOKIE = '2fa';

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
     * Calculate the code, with given secret and point in time
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public function getCode($secret, $timeSlice = null)
    {
        return $this->_getAuth()->getCode($secret, $timeSlice);
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

    /**
     * Get the name to pass to thru Google's QR service
     *
     * @return string
     */
    public function getStoreName()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
        } else {
            $username = Mage::getSingleton('customer/session')->getCustomer()->getName();
        }
        $baseUrl = parse_url(Mage::app()->getStore()->getBaseUrl());
        return $username.'@'.$baseUrl['host'];
    }

    /**
     * Check whether the admin user has valid 2FA cookie
     *
     * @param int|Mage_Admin_Model_User $userId
     * @return bool
     */
    public function isAuthorized($userId)
    {
        if ( ! Mage::app()->getStore()->isAdmin()) {
            return FALSE;
        }
        $cookie = (string)Mage::getSingleton('core/cookie')->get(self::TWO_FACTOR_AUTH_COOKIE);
        if ($cookie) {
            $cookies = explode(',', $cookie);
            $userCookies = Mage::getResourceModel('twofactorauth/user_cookie')->getCookies($userId);
            return (count(array_intersect($cookies, $userCookies)) > 0);
        }
        return FALSE;
    }

    /**
     * Generate 2FA cookie
     *
     * @return string
     */
    public function generateCookie()
    {
        return sha1(Mage::helper('core')->getRandomString(20), FALSE);
    }

    /**
     * Set 2FA cookie
     *
     * @param string $cookie
     * @return void
     */
    public function setCookie($cookie)
    {
        $cookie = (string)$cookie;
        $_cookie = (string)Mage::getSingleton('core/cookie')->get(self::TWO_FACTOR_AUTH_COOKIE);
        if ($_cookie) {
            $_cookie = explode(',', $_cookie);
            if ( ! in_array($cookie, $_cookie)) {
                $_cookie[] = $cookie;
            }
            $_cookie = implode(',', $_cookie);
        } else {
            $_cookie = $cookie;
        }
        Mage::getSingleton('core/cookie')->set(self::TWO_FACTOR_AUTH_COOKIE, $_cookie, time() + 60*60*24*365*10);
    }
}