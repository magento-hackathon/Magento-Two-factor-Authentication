<?php

class MageHackDay_TwoFactorAuth_Model_Resource_User_Question extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('twofactorauth/user_question', 'question_id');
    }

    /**
     * Delete secret questions for the admin user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @return void
     */
    public function deleteQuestions($userId)
    {
        if ($userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        }
        $where = $this->_getWriteAdapter()->quoteInto('user_id = ?', $userId);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
    }

    /**
     * Check whether the user has secret questions
     *
     * @param int|Mage_Admin_Model_User $userId
     * @return bool
     */
    public function hasQuestions($userId)
    {
        if ($userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        }
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(*)')))
            ->where('user_id = ?', $userId);
        return (bool) $this->_getReadAdapter()->fetchOne($select);
    }
}
