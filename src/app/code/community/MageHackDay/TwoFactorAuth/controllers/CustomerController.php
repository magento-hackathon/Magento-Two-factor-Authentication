<?php

class MageHackDay_TwoFactorAuth_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function configureAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('twofactorauth/customer/configure');
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $enable = $this->getRequest()->getParam('enabled', 0);
        $code = $this->getRequest()->getParam('code');
        $secret = $this->getRequest()->getParam('secret');

        try {
            // The user is trying to verify a new account
            if ($enable && $code) {
                $authHelper = Mage::helper('twofactorauth/auth');

                if ($authHelper->verifyCode($code, $secret)) {
                    $customer->setTwofactorauthToken($secret);
                } else {
                    throw new MageHackDay_TwoFactorAuth_Model_Exception('The code you entered was invalid.');
                }
            }
            // The user is turning off 2fa, unset their token
            elseif (!$enable) {
                $customer->setTwofactorauthToken(null);
            }

            $customer
                ->setTwofactorauthEnabled($enable)
                ->save();

            if ($enable && $code) {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
            } else {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
            }
        }
        catch (MageHackDay_TwoFactorAuth_Model_Exception $e)
        {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
        }
        catch (Exception $e)
        {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your settings.'));
        }

        $this->_redirect('twofactorauth/customer/configure');
    }
}