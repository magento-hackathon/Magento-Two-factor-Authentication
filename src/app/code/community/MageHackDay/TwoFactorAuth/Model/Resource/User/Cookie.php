<?php

class MageHackDay_TwoFactorAuth_Model_Resource_User_Cookie extends Mage_Core_Model_Resource_Db_Abstract
{
    const COOKIE_ENTITY_ADMIN = 'admin';
    const COOKIE_ENTITY_CUSTOMER = 'customer';
    protected $fieldMap = null;

    protected function _construct()
    {
        $this->_init('twofactorauth/user_cookie', 'cookie_id');
        $this->fieldMap = array(
            self::COOKIE_ENTITY_ADMIN => 'user_id',
            self::COOKIE_ENTITY_CUSTOMER => 'customer_id',
        );
    }

    private function _getUserId($userId, $entity)
    {
        if ($entity == self::COOKIE_ENTITY_ADMIN && $userId instanceof Mage_Admin_Model_User) {
            $userId = $userId->getId();
        } elseif ($entity == self::COOKIE_ENTITY_CUSTOMER && $userId instanceof Mage_Customer_Model_Customer) {
            $userId = $userId->getId();
        }
        if (!is_int($userId) || intval($userId) <= 0) {
            if(extension_loaded('newrelic')) {
                $msg = 'Invalid ID supplied. Trace follows' . PHP_EOL . PHP_EOL;
                $msg .= debug_backtrace();
                newrelic_notice_error(null, $msg);
            }
            Mage::throwException('Supplied user ID does not resolve to a positive integer');
        }
        return $userId;
    }
    /**
     * Check whether the user has cookies
     *
     * @param int|Mage_Admin_Model_User|Mage_Customer_Model_Customer $userId
     * @param string $entity
     * @return bool
     */
    public function hasCookies($userId, $entity=self::COOKIE_ENTITY_ADMIN)
    {
        $userId = $this->_getUserId($userId, $entity);
        $field = $this->fieldMap[$entity];
        $select = $this->getReadConnection()->select()
            ->from($this->getMainTable(), array('count' => new Zend_Db_Expr('COUNT(*)')))
            ->where("$field = ?", $userId); // No injection here, hardcoded local variable
        return (bool) $this->getReadConnection()->fetchOne($select);
    }

    /**
     * Retrieve cookies for the user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @param string $entity
     * @return array
     */
    public function getCookies($userId, $entity=self::COOKIE_ENTITY_ADMIN)
    {
        $userId = $this->_getUserId($userId, $entity);
        $field = $this->fieldMap[$entity];
        $select = $this->getReadConnection()->select()
            ->from($this->getMainTable(), 'cookie')
            ->where("$field = ?", $userId);
        return (array) $this->getReadConnection()->fetchCol($select);
    }

    /**
     * Save cookie for the user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @param string $cookie
     * @param string $entity
     * @return MageHackDay_TwoFactorAuth_Model_Resource_User_Cookie
     */
    public function saveCookie($userId, $cookie, $entity=self::COOKIE_ENTITY_ADMIN)
    {
        $userId = $this->_getUserId($userId, $entity);
        $field = $this->fieldMap[$entity];
        $data = array(
            $field => (int)$userId,
            'cookie'  => (string)$cookie,
        );
        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $data);
        return $this;
    }

    /**
     * Delete cookies for the user
     *
     * @param int|Mage_Admin_Model_User $userId
     * @param string $entity
     * @return MageHackDay_TwoFactorAuth_Model_Resource_User_Cookie
     */
    public function deleteCookies($userId, $entity=self::COOKIE_ENTITY_ADMIN)
    {
        $userId = $this->_getUserId($userId, $entity);
        $field = $this->fieldMap[$entity];
        $where = $this->_getWriteAdapter()->quoteInto("$field = ?", $userId);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        return $this;
    }
}
