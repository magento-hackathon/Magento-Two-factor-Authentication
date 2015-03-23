<?php

/**
 * Secret questions collection
 *
 * @method MageHackDay_TwoFactorAuth_Model_User_Question getFirstItem()
 */
class MageHackDay_TwoFactorAuth_Model_Resource_User_Question_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('twofactorauth/user_question');
    }

    /**
     * Add user filter
     *
     * @param int|Mage_Admin_Model_User $userId
     * @return MageHackDay_TwoFactorAuth_Model_Resource_User_Question_Collection
     */
    public function addUserFilter($userId)
    {
        if ($userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        }
        $this->addFieldToFilter('user_id', $userId);
        return $this;
    }

    /**
     * Set random order
     *
     * @return MageHackDay_TwoFactorAuth_Model_Resource_User_Question_Collection
     */
    public function setRandomOrder()
    {
        $this->getConnection()->orderRand($this->getSelect());
        return $this;
    }
}
