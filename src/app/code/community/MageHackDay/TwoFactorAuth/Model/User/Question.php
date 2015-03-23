<?php

/**
 * Admin User Question model
 *
 * @method MageHackDay_TwoFactorAuth_Model_Resource_User_Question getResource()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question load()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setQuestionId(int $value)
 * @method int getQuestionId()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setUserId(int $value)
 * @method int getUserId()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setQuestion(string $value)
 * @method string getQuestion()
 * @method MageHackDay_TwoFactorAuth_Model_User_Question setAnswer(string $value)
 * @method string getAnswer()
 */
class MageHackDay_TwoFactorAuth_Model_User_Question extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('twofactorauth/user_question');
    }
}