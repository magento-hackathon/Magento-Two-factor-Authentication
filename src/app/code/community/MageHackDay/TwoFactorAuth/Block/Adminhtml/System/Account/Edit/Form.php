<?php

require_once (Mage::getBaseDir('lib') . DS . 'GoogleAuthenticator' . DS . 'PHPGangsta' . DS . 'GoogleAuthenticator.php');

class MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Account_Edit_Form
    extends Mage_Adminhtml_Block_System_Account_Edit_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        if ( ! Mage::helper('twofactorauth')->isActive()) {
            return $this;
        }

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')->load($userId); /** @var $user Mage_Admin_Model_User */

        $form = $this->getForm();
        $questionsFieldset = $form->addFieldset(
            'secret_questions',
            array('legend' => Mage::helper('twofactorauth')->__('One Time Secret Question'))
        );

        $questionsFieldset->addType('secret_questions', 'MageHackDay_TwoFactorAuth_Block_Adminhtml_System_Account_Form_Questions');

        $questionsCollection = Mage::getResourceModel('twofactorauth/user_question_collection')->addUserFilter($user);
        $questionsCollection->setOrder('question_id', 'ASC');
        $items = array();
        foreach ($questionsCollection as $question) { /** @var $question MageHackDay_TwoFactorAuth_Model_User_Question */
            $items[] = array(
                'question' => $question->getQuestion(),
                'answer'   => $question->getAnswer(),
            );
        }

        $questionsFieldset->addField('questions', 'secret_questions', array(
            'name'      => 'questions',
            'row_count' => 5,
            'value'     => $items,
        ));

        $this->setForm($form);
        return $this;
    }
}