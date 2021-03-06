<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $reAuthenticated = Mage::helper('twofactorauth/auth')->isReAuthenticated();

        $action = $reAuthenticated ? 'qrSubmit' : 'password';
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/'.$action, array('_current' => TRUE)),
            'method' => 'post',
        ));

        if ($reAuthenticated) {
            $form->addType('secret_questions', 'MageHackDay_TwoFactorAuth_Block_Adminhtml_Edit_Form_Element_Questions');
            $form->addField('questions', 'secret_questions', array('name' => 'questions'));
        } else {
            $fieldset = $form->addFieldset('reenter_password', array(
                'legend' => Mage::helper('twofactorauth')->__('Re-enter admin password')
            ));
            $fieldset->addField('password', 'password', array(
                'label'    => Mage::helper('twofactorauth')->__('Password'),
                'required' => TRUE,
                'name'     => 'password',
            ));
        }

        $form->setUseContainer(TRUE);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
