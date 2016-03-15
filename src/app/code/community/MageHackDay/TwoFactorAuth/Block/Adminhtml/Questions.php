<?php

class MageHackDay_TwoFactorAuth_Block_Adminhtml_Questions extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('twofactor/questions.phtml');
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getUser()
    {
        return Mage::getSingleton('admin/session')->getUser();
    }

    /**
     * Retrieve the list of existing questions including answers
     *
     * @return array
     */
    public function getItems()
    {
        if ( ! $this->hasData('items')) {
            $items = array();
            $questionsCollection = Mage::getResourceModel('twofactorauth/user_question_collection')->addUserFilter($this->getUser());
            $questionsCollection->setOrder('question_id', 'ASC');
            foreach ($questionsCollection as $question) { /** @var $question MageHackDay_TwoFactorAuth_Model_User_Question */
                $items[] = array(
                    'question' => $question->getQuestion(),
                    'answer'   => $question->getAnswer(),
                );
            }
            $this->setData('items', $items);
        }
        return $this->getData('items');
    }

    /**
     * Retrieve field name
     *
     * @param int $index
     * @param string $field
     * @return string
     */
    public function getItemName($index, $field)
    {
        return 'questions['.$index.']['.$field.']';
    }

    /**
     * Retrieve item value
     *
     * @param int $iterator
     * @param string $field
     * @return string
     */
    public function getItemValue($iterator, $field)
    {
        $items = $this->getItems();
        $value = '';
        if (isset($items[$iterator][$field])) {
            switch ($field) {
                case 'question': $value = strval($items[$iterator][$field]); break;
                case 'answer': $value  = str_repeat('*', 6); break;
            }
        }
        return $value;
    }
}
