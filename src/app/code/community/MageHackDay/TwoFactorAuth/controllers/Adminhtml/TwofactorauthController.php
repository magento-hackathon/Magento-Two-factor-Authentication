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
    protected function _construct()
    {
        parent::_construct();
        // Define module dependent translate
        $this->setUsedModuleName('MageHackDay_TwoFactorAuth');
    }

    public function interstitialAction()
    {
        if (Mage::helper('twofactorauth/auth')->isAuthorized($this->_getUser())) {
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
        $vSecret = $this->_getUser()->getTwofactorToken();
        if ( ! $vSecret) {
            // User is accessing protected route without configured TFA
            $this->_redirect('*/*/qr');
            return;
        }
        $bValid = $authHelper->verifyCode($vInputCode, $vSecret);
        if ($bValid === FALSE) {
            $this->_getSession()->addError($this->__('Invalid security code.'));
            $this->_redirect('*/*/interstitial');
            return;
        }
        if ($rememberMe) {
            try {
                $cookie = $authHelper->generateCookie();
                Mage::getResourceModel('twofactorauth/user_cookie')->saveCookie($this->_getUser()->getId(), $cookie);
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
            Mage::getResourceModel('twofactorauth/user_cookie')->deleteCookies($this->_getUser());
            $this->_getSession()->addSuccess($this->__('2FA cookies have been successfully deleted.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An error occurred while deleting 2FA cookies.'));
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
            ->addUserFilter($this->_getUser())
            ->setRandomOrder();
        $collection->setCurPage(1)->setPageSize(1);
        $question = $collection->getFirstItem();
        if ( ! $question->getId()) {
            $this->_getSession()->addError($this->__('Cannot load security question.'));
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
            $this->_getSession()->addError($this->__('Unknown question.'));
            return;
        }
        $answer = (string) Mage::app()->getRequest()->getPost('answer');
        if (empty($answer)) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError($this->__('Please enter your answer to the secret question.'));
            return;
        }
        $question = Mage::getModel('twofactorauth/user_question')->load($questionId);
        if ( ! $question->getId() || $question->getUserId() != $this->_getUser()->getId()) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError($this->__('Cannot load the secret question.'));
            return;
        }
        if ($question->getAnswer() != $answer) {
            $this->_redirect('*/*/interstitial');
            $this->_getSession()->addError($this->__('Answer to the secret question is invalid.'));
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
        $helper = Mage::helper('twofactorauth/auth');

        // Process secret token if not yet configured
        if ( ! $this->_getUser()->getTwofactorToken()) {
            $secret = (string) $this->getRequest()->getPost('qr_secret');
            $securityCode = (string) $this->getRequest()->getPost('security_code');
            if ( ! $secret || ! $securityCode) {
                $this->_redirect('*/*/qr');
                return;
            }

            // Verify 2FA security code
            if ($helper->verifyCode($securityCode, $secret)) {
                try {
                    $this->_getUser()->setTwofactorToken($secret)->save();
                    $this->_getSession()->unsTfaNotAssociated();
                }
                catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('An error occurred while saving the security code.'));
                    $this->_redirect('*/*/qr');
                    return;
                }

                // Do not require 2-Factor-Authentication on this computer in the future
                $rememberMe = (bool) $this->getRequest()->getPost('remember_me', FALSE);
                if ($rememberMe) {
                    try {
                        $cookie = $helper->generateCookie();
                        Mage::getResourceModel('twofactorauth/user_cookie')->saveCookie($this->_getUser()->getId(), $cookie);
                        $helper->setCookie($cookie);
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }

            } else {
                $this->_getSession()->addError($this->__('Invalid security code.'));
                $this->_redirect('*/*/qr');
                return;
            }
        }

        // Process secret questions
        $rows = (array) Mage::app()->getRequest()->getPost('questions');
        if ($rows) {
            try {
                $questions = array();
                $invalidQuestion = FALSE;
                foreach ($rows as $index => $row) {
                    if ( ! empty($row['question']) && ! empty($row['answer'])) {
                        $questions[(string)$row['question']] = (string)$row['answer'];
                    } else if ( ! empty($row['question']) || ! empty($row['answer'])) {
                        $invalidQuestion = TRUE;
                    }
                }
                if ($invalidQuestion) {
                    throw new Mage_Core_Exception($this->__('Questions with empty question or answer cannot be saved.'));
                }
                if (count($questions) == 0) {
                    throw new Mage_Core_Exception($this->__('At least one secret question is required.'));
                }
                $resource = Mage::getResourceModel('twofactorauth/user_question');
                try {
                    $resource->beginTransaction();
                    $resource->deleteQuestions($this->_getUser()->getId());
                    foreach ($questions as $question => $answer) {
                        $questionObject = Mage::getModel('twofactorauth/user_question'); /** @var $questionObject MageHackDay_TwoFactorAuth_Model_User_Question */
                        $questionObject->setUserId($this->_getUser()->getId());
                        $questionObject->setQuestion($question);
                        $questionObject->setAnswer($answer);
                        $questionObject->save();
                    }
                    $resource->commit();
                } catch (Exception $e) {
                    $resource->rollBack();
                    Mage::logException($e);
                    throw new Mage_Core_Exception($this->__('An error occurred while saving the secret questions.'));
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/qr');
                return;

            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while saving the secret questions.'));
                $this->_redirect('*/*/qr');
                return;
            }
        }

        $this->_redirect('*');
        return;
    }

    /**
     * Reset Two-Factor Authentication
     */
    public function resetAction()
    {
        if ( ! $this->_getUser()->getTwofactorToken()) {
            $this->_getSession()->addError($this->__('Two-Factor Authentication is not configured so cannot be reset.'));
            $this->_redirect('*/*/qr');
            return;
        }

        $resource = Mage::getResourceModel('twofactorauth/user_question');
        try {
            $resource->beginTransaction();
            $this->_getUser()->setTwofactorToken(NULL)->save();
            Mage::getResourceModel('twofactorauth/user_cookie')->deleteCookies($this->_getUser());
            $resource->deleteQuestions($this->_getUser()->getId());
            $resource->commit();
            $this->_getSession()->addSuccess($this->__('Two-Factor Authentication has been reset.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('An unexpected error occurred while resetting the Two-Factor Authentication.'));
            $resource->rollBack();
        }

        // Logout the user
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());

        $this->_redirect('*/*/qr');
        return;
    }

    /**
     * @return Mage_Admin_Model_User
     */
    protected function _getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }
}