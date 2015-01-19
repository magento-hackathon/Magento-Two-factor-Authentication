<?php

/**
 * Admin User Question model
 *
 * @method MageHackDay_TwoFactorAuth_Model_Resource_User_Question getResource()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setQuestionId(int $value)
 * @method int getQuestionId()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setUserId(int $value)
 * @method int getUserId()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setQuestion(string $value)
 * @method string getQuestion()
 */
class MageHackDay_TwoFactorAuth_Model_User_Question extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('twofactorauth/user_question');
    }

    /**
     * Retrieve decrypted answer
     *
     * @return string
     */
    public function getAnswer()
    {
        $answer = $this->getData('answer');
        if ( ! empty($answer)) {
            $answer = Mage::helper('core')->decrypt($answer);
        }
        return $answer;
    }

    /**
     * Set answer
     *
     * @param string $answer
     * @return MageHackDay_TwoFactorAuth_Model_User_Question
     */
    public function setAnswer($answer)
    {
        if ( ! empty($answer)) {
            $answer = Mage::helper('core')->encrypt($answer);
        }
        $this->setData('answer', $answer);
        return $this;
    }
}