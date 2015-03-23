<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/qrSubmit', array('_current' => TRUE)),
            'method' => 'post',
        ));

        $form->addType('secret_questions', 'MageHackDay_TwoFactorAuth_Block_Adminhtml_Edit_Form_Element_Questions');
        $form->addField('questions', 'secret_questions', array('name' => 'questions'));

        $form->setUseContainer(TRUE);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
