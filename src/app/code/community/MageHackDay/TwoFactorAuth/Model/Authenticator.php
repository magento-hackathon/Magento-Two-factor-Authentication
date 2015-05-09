<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Model_Authenticator extends Mage_Core_Model_Abstract
{

    /*
     * HOTP - counter based
     * TOTP - time based
     */
    public function getToken($username,$tokentype = "TOTP") {
        $token = $this->setUser($username, $tokentype);

        $user = Mage::getModel('admin/user')->loadByUsername($username);
        $user->setTwofactorauthToken($token);
        //$user->save(); //password gets messed up after saving?!
    }

    /*
     * abstract function in GoogleAuthenticator, needs to be defined here
     */
    public function getData($username) {
        $user = Mage::getModel('admin/user')->loadByUsername($username);
        return $user->getTwofactorauthToken()==null ? false : $user->getTwofactorauthToken();
    }

    /*
     * abstract function in GoogleAuthenticator, needs to be defined here
     */
    public function putData($username, $data) {
        $user = Mage::getModel('admin/user')->loadByUsername($username);
        $user->setTwofactorauthToken("test");
        $user->save();
    }

    /*
     * abstract function in GoogleAuthenticator, needs to be defined here
     */
    public function getUsers() {
    }
}