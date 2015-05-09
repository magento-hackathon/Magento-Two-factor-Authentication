<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Permissions_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();

        $fieldset = $form->addFieldset('ga2fa_fieldset', array('legend'=>$this->__('Two Factor Authentication')));

        $fieldset->addField('twofactorauth', 'select', array(
          'label'     => Mage::helper('twofactorauth')->__('Use Google Authenticator'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'twofactorauth',
          'onclick' => "",
          'onchange' => "",
          'value'  => '1',
          'values' => array('0' => 'No','1' => 'Yes'),
          'disabled' => false,
          'readonly' => false,
          'after_element_html' => '<small>When using the Two Factor Authentication, a 6-digit code needs to be entered in addition to username and password during login. Two Factor Authentication Google provides the Authenticator app for Android, BlackBerry and iOS.</small>',
        ));

        $model = Mage::registry('permissions_user');
        $data = $model->getData();
        unset($data['password']);
        $form->setValues($data);

        return $this;
    }
}