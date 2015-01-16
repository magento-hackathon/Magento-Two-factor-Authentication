<?php

class MageHackDay_TwoFactorAuth_Model_Resource_User_Cookie extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('twofactorauth/user_cookie', 'cookie_id');
    }

    /**
     * Retrieve cookies for the user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @return array
     */
    public function getCookies($userId)
    {
        if ($userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        }
        $select = $this->getReadConnection()->select()
            ->from($this->getMainTable(), 'cookie')
            ->where('user_id = ?', $userId);
        return (array) $this->getReadConnection()->fetchCol($select);
    }

    /**
     * Save cookie for the user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @param string $cookie
     * @return MageHackDay_TwoFactorAuth_Model_Resource_User_Cookie
     */
    public function saveCookie($userId, $cookie)
    {
        if ($userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        }
        $data = array(
            'user_id' => (int)$userId,
            'cookie'  => (string)$cookie,
        );
        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data);
        return $this;
    }

    /**
     * Delete cookies for the user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @return MageHackDay_TwoFactorAuth_Model_Resource_User_Cookie
     */
    public function deleteCookies($userId)
    {
        if ($userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        }
        $where = $this->_getWriteAdapter()->quoteInto('user_id = ?', $userId);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        return $this;
    }
}
