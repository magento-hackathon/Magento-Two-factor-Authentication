<?php

/**
 * adminhtml controller to enforce Two Factor Authentication
 *
 * @category    MageHackDay
 * @package     MageHackDay_TwoFactorAuth
 * @author      Jonathan Day <jonathan@aligent.com.au>
 */
class MageHackDay_TwoFactorAuth_Adminhtml_TwofactorauthController extends Mage_Adminhtml_Controller_Action
{
    public function interstitialAction()
    {
        $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */
        if (Mage::helper('twofactorauth/auth')->isAuthorized($user)) {
            $this->_getSession()->unsTfaNotEntered(TRUE);
            $this->_redirect('*');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function verifyAction()
    {
        $oRequest = Mage::app()->getRequest();
        $vInputCode = $oRequest->getPost('input_code', NULL);
        $rememberMe = (bool) $oRequest->getPost('remember_me', FALSE);
        $authHelper = Mage::helper('twofactorauth/auth');
        $oUser = Mage::getSingleton('admin/session')->getUser(); /** @var $oUser Mage_Admin_Model_User */
        $vSecret = $oUser->getTwofactorToken();
        if ( ! $vSecret) {
            // User is accessing protected route without configured TFA
            $this->_redirect('*/*/qr');
            return;
        }
        $bValid = $authHelper->verifyCode($vInputCode, $vSecret);
        if ($bValid === FALSE) {
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Invalid security code.'));
            $this->_redirect('*/*/interstitial');
            return;
        }
        if ($rememberMe) {
            try {
                $cookie = $authHelper->generateCookie();
                Mage::getResourceModel('twofactorauth/user_cookie')->saveCookie($oUser->getId(), $cookie);
                $authHelper->setCookie($cookie);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->_getSession()->unsTfaNotEntered();
        $this->_redirect('*');
        return;
    }

    /**
     * Clear cookies for the current user
     */
    public function clearCookiesAction()
    {
        try {
            Mage::getResourceModel('twofactorauth/user_cookie')->deleteCookies(Mage::getSingleton('admin/session')->getUser());
            $this->_getSession()->addSuccess(Mage::helper('twofactorauth')->__('2FA cookies have been successfully deleted.'));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('An error occurred while deleting 2FA cookies.'));
            Mage::logException($e);
        }

        $this->_redirect('*/system_account/index');
    }

    /**
     * Display one time secret question
     */
    public function questionAction()
    {
        $collection = Mage::getResourceModel('twofactorauth/user_question_collection')
            ->addUserFilter(Mage::getSingleton('admin/session')->getUser())
            ->setRandomOrder();
        $collection->setCurPage(1)->setPageSize(1);
        $question = $collection->getFirstItem();
        if ( ! $question->getId()) {
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Cannot load security question.'));
            $this->_redirect('*/*/interstitial');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check answer to the one time secret question
     */
    public function answerAction()
    {
        $questionId = (int) Mage::app()->getRequest()->getPost('question_id');
        if ( ! $questionId) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Unknown question.'));
            return;
        }
        $answer = (string) Mage::app()->getRequest()->getPost('answer');
        if (empty($answer)) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Please enter your answer to the secret question.'));
            return;
        }
        $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */
        $question = Mage::getModel('twofactorauth/user_question')->load($questionId);
        if ( ! $question->getId() || $question->getUserId() != $user->getId()) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Cannot load the secret question.'));
            return;
        }
        if ($question->getAnswer() != $answer) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Answer to the secret question is invalid.'));
            return;
        }

        Mage::getSingleton('adminhtml/session')->setTfaNotEntered(FALSE);
        $question->delete();
        $this->_redirect('*');
        return;
    }

    /**
     * QR code action
     */
    public function qrAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Submit QR secret code
     */
    public function qrSubmitAction()
    {
        $secret = (string) $this->getRequest()->getPost('qr_secret');
        $securityCode = (string) $this->getRequest()->getPost('security_code');
        if ( ! $secret || ! $securityCode) {
            $this->_redirect('*/*/qr');
            return;
        }

        if (Mage::helper('twofactorauth/auth')->verifyCode($securityCode, $secret)) {
            try {
                $user = Mage::getSingleton('admin/session')->getUser(); /** @var $user Mage_Admin_Model_User */
                $user->setTwofactorToken($secret)->save();
                $this->_getSession()->unsTfaNotAssociated();
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            $this->_getSession()->addError(Mage::helper('twofactorauth')->__('Invalid security code.'));
            $this->_redirect('*/*/qr');
            return;
        }

        $this->_redirect('*');
        return;
    }
}