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
}